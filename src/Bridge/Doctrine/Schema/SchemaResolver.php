<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Schema;

use Doctrine\ORM\Mapping\ClassMetadata;
use InvalidArgumentException;

final readonly class SchemaResolver
{

	/**
	 * @param ClassMetadata<object> $classMetadata
	 */
	public function __construct(
		private ClassMetadata $classMetadata,
	)
	{
	}

	public function table(): string
	{
		return $this->classMetadata->getTableName();
	}

	public function column(string $field): string
	{
		if ($this->classMetadata->hasField($field)) {
			return $this->classMetadata->getColumnName($field);
		}

		if ($this->classMetadata->hasAssociation($field)) {
			return $this->classMetadata->getSingleAssociationJoinColumnName($field);
		}

		throw new InvalidArgumentException(
			sprintf('Field "%s" not found in entity "%s".', $field, $this->classMetadata->name),
		);
	}

	/**
	 * @param string[] $fields
	 */
	public function columns(array $fields, string $joinWith = ', '): string
	{
		return implode($joinWith, array_map(fn (string $field): string => $this->column($field), $fields));
	}

}
