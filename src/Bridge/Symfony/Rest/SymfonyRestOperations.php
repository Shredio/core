<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Rest;

use Doctrine\ORM\EntityManagerInterface;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Shredio\Core\Bridge\Doctrine\EntityManagerRegistry;
use Shredio\Core\Common\Debug\DebugHelper;
use Shredio\Core\Entity\EntityFactory;
use Shredio\Core\Environment\AppEnvironment;
use Shredio\Core\Fixture\StagingReadyFixture;
use Shredio\Core\Package\Instruction\SerializationInstruction;
use Shredio\Core\Package\Response\SourceResponse;
use Shredio\Core\Rest\Operation\EntityOperation;
use Shredio\Core\Rest\RestOperationBuilder;
use Shredio\Core\Rest\RestOperations;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use WebChemistry\Fixtures\Bridge\Doctrine\Key\DoctrineFixtureKey;
use WebChemistry\Fixtures\Fixture;
use WebChemistry\Fixtures\FixtureRegistry;

/**
 * @template T of object
 * @implements RestOperations<T>
 */
final readonly class SymfonyRestOperations implements RestOperations
{

	/**
	 * @param class-string<T> $entityName
	 * @param mixed[] $defaultOptions
	 */
	public function __construct(
		private string $entityName,
		private ?string $guardNamespace,
		private EntityManagerRegistry $registry,
		private EntityFactory $entityFactory,
		private AppEnvironment $appEnv,
		private Security $security,
		private ?FixtureRegistry $fixtureRegistry,
		private array $defaultOptions = [],
	)
	{
	}

	public function create(array $values, ?int $guardMode = null, array $options = []): ResponseInterface
	{
		$options = array_merge($this->defaultOptions, $options);

		$guardMode ??= $this->guardNamespace ? self::GuardOnAttribute : self::NoGuard;

		if ($guardMode & self::GuardOnAttribute) {
			$this->requirePermission('create');
		}

		$entity = $this->entityFactory->create($this->entityName, $values);

		if ($guardMode & self::GuardOnEntity) {
			$this->requirePermission('create', $entity);
		}

		if ($options['validationMode'] ?? false) {
			return new Response(204);
		}

		$this->callOnEntity($entity, $options);
		$this->changeEntity($entity, EntityOperation::Create, $options);

		return new SourceResponse($entity, [
			new SerializationInstruction($options[self::SerializationContext] ?? []),
		]);
	}

	public function read(mixed $id, ?int $guardMode = null, array $options = []): ResponseInterface
	{
		$options = array_merge($this->defaultOptions, $options);

		$guardMode ??= $this->guardNamespace ? self::GuardOnEntity : self::NoGuard;

		if ($guardMode & self::GuardOnAttribute) {
			$this->requirePermission('read');
		}

		$entity = $this->getEntity($id);
		$this->callOnEntity($entity, $options);

		if ($guardMode & self::GuardOnEntity) {
			$this->requirePermission('read', $entity);
		}

		return new SourceResponse($entity, [
			new SerializationInstruction($options[self::SerializationContext] ?? []),
		]);
	}

	public function update(mixed $id, array $values, ?int $guardMode = null, array $options = []): ResponseInterface
	{
		$options = array_merge($this->defaultOptions, $options);

		$guardMode ??= $this->guardNamespace ? self::GuardOnEntity : self::NoGuard;

		if ($guardMode & self::GuardOnAttribute) {
			$this->requirePermission('update');
		}

		$entity = $this->getEntity($id);

		if ($guardMode & self::GuardOnEntity) {
			$this->requirePermission('update', $entity);
		}

		$entity = $this->entityFactory->update($entity, $values);

		if ($options['validationMode'] ?? false) {
			return new Response(204);
		}

		$this->callOnEntity($entity, $options);
		$this->changeEntity($entity, EntityOperation::Update, $options);

		return new SourceResponse($entity, [
			new SerializationInstruction($options[self::SerializationContext] ?? []),
		]);
	}

	public function delete(mixed $id, ?int $guardMode = null, array $options = []): ResponseInterface
	{
		$options = array_merge($this->defaultOptions, $options);

		$guardMode ??= $this->guardNamespace ? self::GuardOnEntity : self::NoGuard;

		if ($guardMode & self::GuardOnAttribute) {
			$this->requirePermission('delete');
		}

		$entity = $this->getEntity($id);
		$this->callOnEntity($entity, $options);

		if ($guardMode & self::GuardOnEntity) {
			$this->requirePermission('delete', $entity);
		}

		$this->changeEntity($entity, EntityOperation::Delete, $options);

		return new Response(204);
	}

	/**
	 * @return Fixture<object>|null
	 */
	private function getFixture(): ?Fixture
	{
		if (!$this->fixtureRegistry) {
			return null;
		}

		foreach ($this->fixtureRegistry->getAll() as $fixture) {
			$key = $fixture->getKey();

			if (!$key instanceof DoctrineFixtureKey) {
				continue;
			}

			if ($key->getClassName() === $this->entityName) {
				return $fixture;
			}
		}

		return null;
	}

	private function requirePermission(string $permission, ?object $source = null): void
	{
		if (!$source && !$this->guardNamespace) {
			return;
		}

		if ($source) {
			$granted = $this->security->isGranted($permission, $source);
		} else {
			$permission = $this->guardNamespace . '.' . $permission;

			$granted = $this->security->isGranted($permission);
		}

		if (!$granted) {
			throw new AccessDeniedException(sprintf('Permission %s denied', $permission));
		}
	}

	private function getEntityManager(): EntityManagerInterface
	{
		return $this->registry->getManagerForClass($this->entityName);
	}

	/**
	 * @param mixed[] $values
	 * @return RestOperationBuilder<T>
	 */
	public function buildCreate(array $values): RestOperationBuilder
	{
		/** @var RestOperationBuilder<T> */
		return new RestOperationBuilder(
			'create',
			fn (int $guardMode, array $options): ResponseInterface => $this->create($values, $guardMode, $options),
			$this->guardNamespace ? self::GuardOnAttribute : self::NoGuard,
		);
	}

	/**
	 * @return RestOperationBuilder<T>
	 */
	public function buildRead(mixed $id): RestOperationBuilder
	{
		/** @var RestOperationBuilder<T> */
		return new RestOperationBuilder(
			'read',
			fn (int $guardMode, array $options): ResponseInterface => $this->read($id, $guardMode, $options),
			$this->guardNamespace ? self::GuardOnEntity : self::NoGuard,
		);
	}

	/**
	 * @param mixed[] $values
	 * @return RestOperationBuilder<T>
	 */
	public function buildUpdate(mixed $id, array $values): RestOperationBuilder
	{
		/** @var RestOperationBuilder<T> */
		return new RestOperationBuilder(
			'update',
			fn (int $guardMode, array $options): ResponseInterface => $this->update($id, $values, $guardMode, $options),
			$this->guardNamespace ? self::GuardOnEntity : self::NoGuard,
		);
	}

	/**
	 * @return RestOperationBuilder<T>
	 */
	public function buildDelete(mixed $id): RestOperationBuilder
	{
		/** @var RestOperationBuilder<T> */
		return new RestOperationBuilder(
			'delete',
			fn (int $guardMode, array $options): ResponseInterface => $this->delete($id, $guardMode, $options),
			$this->guardNamespace ? self::GuardOnEntity : self::NoGuard,
		);
	}

	/**
	 * @return T
	 */
	private function getEntity(mixed $id): object
	{
		$entity = $this->findEntity($id);

		if (!$entity) {
			throw new NotFoundHttpException(
				sprintf('Entity %s with id %s not found', $this->entityName, DebugHelper::stringifyMixed($id)),
			);
		}

		return $entity;
	}

	/**
	 * @param T $entity
	 * @param mixed[] $options
	 * @return T
	 */
	private function callOnEntity(object $entity, array $options): object
	{
		$fn = $options[self::OnEntity] ?? null;

		if ($fn) {
			/** @var T|null $return */
			$return = $fn($entity);

			if (is_object($return)) {
				$entity = $return;
			}
		}

		return $entity;
	}

	/**
	 * @return T|null
	 */
	private function findEntity(mixed $id): ?object
	{
		if ($this->appEnv->isStaging() && ($fixture = $this->getFixture()) && $fixture instanceof StagingReadyFixture) {
			if (is_array($id)) {
				/** @var T */
				return $fixture->make($id);
			} else {
				$classMetadata = $this->getEntityManager()->getClassMetadata($this->entityName);

				/** @var T */
				return $fixture->make([
					$classMetadata->getSingleIdentifierFieldName() => $id,
				]);
			}
		}

		return $this->getEntityManager()->find($this->entityName, $id);
	}

	/**
	 * @param mixed[] $options
	 */
	private function changeEntity(object $entity, EntityOperation $operation, array $options): void
	{
		$em = $this->getEntityManager();

		if ($operation === EntityOperation::Delete) {
			$em->remove($entity);
		} else {
			$em->persist($entity);
		}

		if ($callback = $options[self::BeforeFlush] ?? null) {
			$callback($entity, $em);
		}

		$em->flush();

		if ($callback = $options[self::AfterFlush] ?? null) {
			$callback($entity, $em);
		}
	}

}
