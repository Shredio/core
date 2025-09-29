<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Rest;

use Shredio\Auth\Context\CurrentUserContext;
use Shredio\Core\Bridge\Doctrine\EntityManagerRegistry;
use Shredio\Core\Entity\EntityFactory;
use Shredio\Core\Environment\AppEnvironment;
use Shredio\Core\Path\Directories;
use Shredio\Core\Rest\RestOperations;
use Shredio\Core\Rest\RestOperationsFactory;
use Symfony\Bundle\SecurityBundle\Security;
use WebChemistry\Fixtures\FixtureRegistry;

final readonly class SymfonyRestOperationsFactory implements RestOperationsFactory
{

	public function __construct(
		private EntityManagerRegistry $registry,
		private EntityFactory $entityFactory,
		private AppEnvironment $appEnv,
		private Security $security,
		private Directories $directories,
		private CurrentUserContext $currentUserContext,
		private ?FixtureRegistry $fixtureRegistry,
	)
	{
	}

	/**
	 * @template T of object
	 * @param class-string<T> $entityName
	 * @return RestOperations<T>
	 */
	public function create(string $entityName, ?string $guardNamespace = null, array $defaultOptions = []): RestOperations
	{
		/** @var SymfonyRestOperations<T> */
		return new SymfonyRestOperations(
			$entityName,
			$guardNamespace,
			$this->registry,
			$this->entityFactory,
			$this->appEnv,
			$this->security,
			$this->directories,
			$this->currentUserContext,
			$this->fixtureRegistry,
			$defaultOptions,
		);
	}

}
