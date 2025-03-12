<?php declare(strict_types = 1);

namespace Shredio\Core\Entity;

use InvalidArgumentException;
use ReflectionClass;
use Shredio\Core\Attribute\PreValidate;
use Shredio\Core\Common\Reflection\ReflectionHelper;
use Shredio\Core\Entity\Metadata\ContextExtractor;
use Shredio\Core\Entity\Metadata\CreateContext;
use Shredio\Core\Entity\Metadata\UpdateContext;
use Shredio\Core\Exception\BadRequestException;
use Shredio\Core\Exception\HttpException;
use Shredio\Core\Exception\InvalidDataException;
use Shredio\Core\Payload\ErrorsPayload;
use Shredio\Core\Payload\ErrorThrowType;
use Shredio\Core\Payload\FieldErrorPayload;
use Shredio\Core\Payload\InternalErrorPayload;
use Shredio\Core\Validator\ValidationGroupProvider;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class SymfonyEntityFactory implements EntityFactory
{

	public function __construct(
		private DenormalizerInterface $denormalizer,
		private ValidatorInterface $validator,
		private TranslatorInterface $translator,
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

		$context['errors'] = $errors = new ErrorsPayload();

		try {
			$object = $this->denormalizer->denormalize($data, $className, context: $context);
		} catch (MissingConstructorArgumentsException $exception) {
			$errors = new ErrorsPayload();

			foreach ($exception->getMissingConstructorArguments() as $field) {
				$errors->addError(InternalErrorPayload::fromThrowable(
					$exception,
					new FieldErrorPayload($this->translator->trans('validators.required.message'), $field),
				));
			}

			$errors->throw(ErrorThrowType::Validation);
		} catch (ExtraAttributesException $exception) {
			$message = sprintf('Extra values: %s', implode(', ', $exception->getExtraAttributes()));

			throw new InvalidDataException($message, $exception, [
				'extraAttributes' => $exception->getExtraAttributes(),
			]);
		} catch (NotNormalizableValueException $exception) {
			throw new InvalidDataException($exception->getMessage(), $exception);
		}

		if ($object === null) {
			throw new BadRequestException('Returned entity is a null.');
		}

		if (!$object instanceof $className) {
			throw new InvalidArgumentException(
				sprintf('Entity must be an instance of %s, %s given.', $className, get_debug_type($object)),
			);
		}

		$this->tryToRaiseValidationException($this->validate($object), $errors);

		return $object;
	}

	private function tryToRaiseValidationException(ConstraintViolationListInterface $list, ErrorsPayload $errors = new ErrorsPayload()): void
	{
		if ($list->count()) {
			foreach ($list as $violation) {
				$constraint = $violation->getConstraint();
				$message = (string) $violation->getMessage();
				$propertyPath = $violation->getPropertyPath();

				if ($constraint) {
					$this->tryToRaiseCustomException($constraint, $message, $propertyPath);
				}

				$errors->addError(new FieldErrorPayload($message, $propertyPath));
			}

			$errors->throw(ErrorThrowType::Validation);
		} else if (!$errors->isOk()) {
			$errors->throw(ErrorThrowType::Validation);
		}
	}

	private function tryToRaiseCustomException(Constraint $constraint, string $message, string $propertyPath): void
	{
		$payload = $constraint->payload;

		if (!is_array($payload) || !isset($payload['httpCode'])) {
			return;
		}

		$httpCode = $payload['httpCode'];

		if ($httpCode === 400) {
			throw new BadRequestException(sprintf('%s: %s', $propertyPath, $message));
		}
	}

	private function validate(object $object): ConstraintViolationListInterface
	{
		if ($object instanceof ValidationGroupProvider) {
			$groups = $object->provideValidationGroups();
		} else {
			$groups = null;
		}

		return $this->validator->validate($object, groups: $groups);
	}

}
