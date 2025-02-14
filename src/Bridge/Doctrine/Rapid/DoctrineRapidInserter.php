<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Rapid;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Shredio\Core\Bridge\Doctrine\Rapid\Trait\ExecuteDoctrineOperation;
use Shredio\Core\Bridge\Doctrine\Rapid\Trait\MapDoctrineColumn;
use Shredio\Core\Database\Rapid\BaseRapidInserter;
use Shredio\Core\Database\Rapid\Platform\RapidOperationPlatform;

final class DoctrineRapidInserter extends BaseRapidInserter
{

	use ExecuteDoctrineOperation;
	use MapDoctrineColumn;

	/** @var ClassMetadata<object> */
	private readonly ClassMetadata $metadata;

	private ?RapidOperationPlatform $platform = null;

	/**
	 * @param mixed[] $options
	 */
	public function __construct(
		string $entity,
		private readonly EntityManagerInterface $em,
		array $options = [],
	)
	{
		$this->metadata = $this->em->getClassMetadata($entity);

		parent::__construct(
			$options['table'] ?? $this->metadata->getTableName(),
			new DoctrineOperationEscaper($this->em),
			$this->metadata->getIdentifierColumnNames(),
			$options,
		);
	}

	protected function getPlatform(): RapidOperationPlatform
	{
		return $this->platform ??= DoctrineRapidOperationPlatformFactory::create(
			$this->em->getConnection()->getDatabasePlatform(),
		);
	}

	/**
	 * @param string[] $fields
	 * @return string[]
	 */
	protected function filterFieldsToUpdate(array $fields): array
	{
		$filtered = array_diff($fields, $this->metadata->getIdentifierFieldNames());

		if (!$filtered) {
			return $fields;
		}

		return $filtered;
	}

}
