<?php declare(strict_types = 1);

namespace Shredio\Core\Entity;

use InvalidArgumentException;
use ReflectionClass;
use Shredio\Core\Attribute\PreValidate;
use Shredio\Core\Common\Reflection\ReflectionHelper;
use Shredio\Core\Entity\Metadata\ContextExtractor;
use Shredio\Core\Entity\Metadata\CreateContext;
use Shredio\Core\Entity\Metadata\UpdateContext;
use Shredio\Core\Exception\HttpException;
use Shredio\Core\Exception\InvalidDataException;
use Shredio\Core\Exception\ValidationException;
use Shredio\Core\Payload\ErrorsPayload;
use Shredio\Core\Payload\FieldErrorPayload;
use Shredio\Core\Payload\InternalErrorPayload;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class SymfonyEntityFactory implements EntityFactory
{

	public function __construct(
		private DenormalizerInterface $denormalizer,
		private ValidatorInterface $validator,
		private ContextExtractor $contextExtractor,
		private bool $strict = true,
	)
	{
	}

	/**
	 * @template TEntity of object
	 * @param class-string<TEntity> $className
	 * @param mixed[] $data
	 * @return TEntity
	 *
	 * @throws HttpException
	 */
	public function create(string $className, array $data): object
	{
		$this->preValidate($className, $data);

		$context = $this->contextExtractor->extract($className, CreateContext::class);
		$context['operation'] = 'create';

		return $this->customized($className, $data, $context);
	}

	/**
	 * @template TEntity of object
	 * @param TEntity $entity
	 * @param mixed[] $data
	 * @return TEntity
	 *
	 * @throws HttpException
	 */
	public function update(object $entity, array $data): object
	{
		$this->preValidate($entity::class, $data);

		$context = $this->contextExtractor->extract($entity::class, UpdateContext::class);
		$context[AbstractNormalizer::OBJECT_TO_POPULATE] = $entity;
		$context['operation'] = 'update';

		return $this->customized($entity::class, $data, $context);
	}

	/**
	 * @param class-string $className
	 * @param mixed[] $data
	 */
	private function preValidate(string $className, array $data): void
	{
		$attribute = ReflectionHelper::getAttribute(new ReflectionClass($className), PreValidate::class);
		
		if (!$attribute) {
			return;
		}

		$validator = $this->validator->startContext();

		foreach ($data as $property => $value) {
			$validator->atPath($property);

			$validator->validatePropertyValue($className, $property, $value, $attribute->groups);
		}

		$this->tryToRaiseValidationException($validator->getViolations());
	}

	/**
	 * @template TEntity of object
	 * @param class-string<TEntity> $className
	 * @param mixed[] $data
	 * @param mixed[] $context
	 * @return TEntity
	 *
	 * @throws HttpException
	 */
	private function customized(string $className, array $data, array $context = []): object
	{
		if ($this->strict) {
			$context[AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES] ??= false;
		}

		try {
			$object = $this->denormalizer->denormalize($data, $className, context: $context);
		} catch (MissingConstructorArgumentsException $exception) {
			$errors = new ErrorsPayload();

			foreach ($exception->getMissingConstructorArguments() as $id) {
				$errors->addError(InternalErrorPayload::fromThrowable(
					$exception,
					new FieldErrorPayload('This field is required.', $id),
				));
			}

			throw new ValidationException($errors);
		} catch (ExtraAttributesException $exception) {
			throw new InvalidDataException($exception);
		}

		if (!is_a($object, $className, true)) {
			throw new InvalidArgumentException(
				sprintf('Entity must be an instance of %s, %s given.', $className, get_debug_type($object)),
			);
		}

		$this->tryToRaiseValidationException($this->validator->validate($object));

		/** @var TEntity */
		return $object;
	}

	private function tryToRaiseValidationException(ConstraintViolationListInterface $list): void
	{
		if ($list->count()) {
			$errors = new ErrorsPayload();

			foreach ($list as $violation) {
				$errors->addError(new FieldErrorPayload((string) $violation->getMessage(), $violation->getPropertyPath()));
			}

			throw new ValidationException($errors);
		}
	}

}
