<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Rapid;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Shredio\Core\Bridge\Doctrine\Rapid\Trait\ExecuteDoctrineOperation;
use Shredio\Core\Bridge\Doctrine\Rapid\Trait\MapDoctrineColumn;
use Shredio\Core\Database\Rapid\BaseRapidUpdater;

final class DoctrineRapidUpdater extends BaseRapidUpdater
{

	use ExecuteDoctrineOperation;
	use MapDoctrineColumn;

	/** @var ClassMetadata<object> */
	private readonly ClassMetadata $metadata;

	/**
	 * @param string[] $conditions
	 */
	public function __construct(
		string $entity,
		array $conditions,
		private readonly EntityManagerInterface $em,
	)
	{
		$this->metadata = $this->em->getClassMetadata($entity);

		parent::__construct($this->metadata->getTableName(), $conditions, new DoctrineOperationEscaper($this->em));
	}

}
