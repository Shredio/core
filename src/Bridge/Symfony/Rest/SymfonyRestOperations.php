<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Rest;

use Doctrine\ORM\EntityManagerInterface;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Shredio\Core\Bridge\Doctrine\EntityManagerRegistry;
use Shredio\Core\Common\Debug\DebugHelper;
use Shredio\Core\Entity\EntityFactory;
use Shredio\Core\Environment\AppEnvironment;
use Shredio\Core\Package\Instruction\SerializationInstruction;
use Shredio\Core\Package\Response\SourceResponse;
use Shredio\Core\Rest\RestOperations;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use WebChemistry\Fixtures\Bridge\Doctrine\Key\DoctrineFixtureKey;
use WebChemistry\Fixtures\Fixture;
use WebChemistry\Fixtures\FixtureRegistry;

final readonly class SymfonyRestOperations implements RestOperations
{

	/**
	 * @param class-string $entityName
	 */
	public function __construct(
		private string $entityName,
		private ?string $guardNamespace,
		private EntityManagerRegistry $registry,
		private EntityFactory $entityFactory,
		private AppEnvironment $appEnv,
		private Security $security,
		private ?FixtureRegistry $fixtureRegistry,
	)
	{
	}

	public function create(array $values, int $guardMode = self::GuardOnAttribute): ResponseInterface
	{
		if ($guardMode & self::GuardOnAttribute) {
			$this->requirePermission('create');
		}

		$entity = $this->entityFactory->create($this->entityName, $values);

		if ($guardMode & self::GuardOnEntity) {
			$this->requirePermission('create', $entity);
		}

		$em = $this->getEntityManager();
		$em->persist($entity);
		$em->flush();

		return new SourceResponse($entity, [
			new SerializationInstruction(),
		]);
	}

	public function read(mixed $id, int $guardMode = self::GuardOnEntity): ResponseInterface
	{
		if ($guardMode & self::GuardOnAttribute) {
			$this->requirePermission('read');
		}

		$entity = $this->findEntity($id);

		if (!$entity) {
			throw new NotFoundHttpException(
				sprintf('Entity %s with id %s not found', $this->entityName, DebugHelper::stringifyMixed($id)),
			);
		}

		if ($guardMode & self::GuardOnEntity) {
			$this->requirePermission('read', $entity);
		}

		return new SourceResponse($entity, [
			new SerializationInstruction(),
		]);
	}

	public function update(mixed $id, array $values, int $guardMode = self::GuardOnEntity): ResponseInterface
	{
		if ($guardMode & self::GuardOnAttribute) {
			$this->requirePermission('update');
		}

		$entity = $this->findEntity($id);

		if (!$entity) {
			throw new NotFoundHttpException(
				sprintf('Entity %s with id %s not found', $this->entityName, DebugHelper::stringifyMixed($id)),
			);
		}

		if ($guardMode & self::GuardOnEntity) {
			$this->requirePermission('update', $entity);
		}

		$entity = $this->entityFactory->update($entity, $values);

		$em = $this->getEntityManager();
		$em->persist($entity);
		$em->flush();

		return new SourceResponse($entity, [
			new SerializationInstruction(),
		]);
	}

	public function delete(mixed $id, int $guardMode = self::GuardOnEntity): ResponseInterface
	{
		if ($guardMode & self::GuardOnAttribute) {
			$this->requirePermission('delete');
		}

		$entity = $this->findEntity($id);

		if (!$entity) {
			throw new NotFoundHttpException(
				sprintf('Entity %s with id %s not found', $this->entityName, DebugHelper::stringifyMixed($id)),
			);
		}

		if ($guardMode & self::GuardOnEntity) {
			$this->requirePermission('delete', $entity);
		}

		$em = $this->getEntityManager();
		$em->remove($entity);
		$em->flush();

		return new Response(204);
	}

	private function findEntity(mixed $id): ?object
	{
		if ($this->appEnv->isStaging() && $fixture = $this->getFixture()) {
			if (is_array($id)) {
				return $fixture->make($id);
			} else {
				$classMetadata = $this->getEntityManager()->getClassMetadata($this->entityName);

				return $fixture->make([
					$classMetadata->getSingleIdentifierFieldName() => $id,
				]);
			}
		}

		return $this->getEntityManager()->find($this->entityName, $id);
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

}
