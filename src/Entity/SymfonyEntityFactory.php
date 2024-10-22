<?php declare(strict_types = 1);

namespace Shredio\Core\Entity;

use Shredio\Core\Entity\Exception\InvalidDataException;
use Shredio\Core\Entity\Exception\ValidationException;
use Shredio\Core\Entity\Metadata\ContextExtractor;
use Shredio\Core\Entity\Metadata\CreateContext;
use Shredio\Core\Entity\Metadata\UpdateContext;
use InvalidArgumentException;
use Shredio\Core\Exception\HttpException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class SymfonyEntityFactory implements EntityFactory
{

	public function __construct(
		private Serializer $serializer,
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
		$context = $this->contextExtractor->extract($entity::class, UpdateContext::class);
		$context[AbstractNormalizer::OBJECT_TO_POPULATE] = $entity;
		$context['operation'] = 'update';

		return $this->customized($entity::class, $data, $context);
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
			$object = $this->serializer->denormalize($data, $className, context: $context);
		} catch (MissingConstructorArgumentsException $exception) { // @phpstan-ignore-line -- Exception is thrown
			$errors = [];

			foreach ($exception->getMissingConstructorArguments() as $id) {
				$errors[$id] = ['This field is required.'];
			}

			throw new ValidationException($errors);
		} catch (ExtraAttributesException $exception) { // @phpstan-ignore-line -- Exception is thrown
			throw new InvalidDataException($className, $exception);
		}

		if (!is_a($object, $className, true)) {
			throw new InvalidArgumentException(
				sprintf('Entity must be an instance of %s, %s given.', $className, $object::class),
			);
		}

		/** @var ConstraintViolationList $violationList */
		$violationList = $this->validator->validate($object);

		if ($violationList->count()) {
			$errors = [];

			foreach ($violationList as $violation) {
				$errors[$violation->getPropertyPath()][] = $violation->getMessage();
			}

			throw new ValidationException($errors);
		}

		/** @var TEntity */
		return $object;
	}

}
