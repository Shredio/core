<?php declare(strict_types = 1);

namespace Shredio\Core\Database\Rapid;

use InvalidArgumentException;
use LogicException;

abstract class BaseRapidInserter implements RapidInserter
{

	public const string ColumnsToUpdate = 'columnsToUpdate';
	public const string Mode = 'mode';
	public const int ModeNormal = 0;
	public const int ModeUpsert = 1;
	public const int ModeInsertNonExisting = 2;

	protected string $sql = '';

	protected int $mode;

	protected readonly string $table;

	/** @var string[] */
	protected array $columnsToUpdate = [];

	/** @var string[] */
	private array $required = [];

	/**
	 * @param mixed[] $options
	 */
	public function __construct(
		string $table,
		private readonly OperationEscaper $escaper,
		array $options = [],
	)
	{
		$this->table = $this->escaper->escapeColumn($table);
		$this->columnsToUpdate = $options[self::ColumnsToUpdate] ?? [];
		$this->mode = $options[self::Mode] ?? self::ModeNormal;
	}

	public function addRaw(array $values): static
	{
		return $this->add(new OperationValues($values));
	}

	public function add(OperationValues $values): static
	{
		$this->checkCorrectOrder($values);

		if ($this->sql === '') {
			$this->sql .= $this->sqlForStart($values);
		}

		$this->sql .= $this->buildValues($values) . ",\n";

		return $this;
	}

	public function execute(): void
	{
		$sql = $this->getSql();

		if ($sql === '') {
			return;
		}

		$this->executeSql($sql);
		$this->reset();
	}

	public function getSql(): string
	{
		$sql = $this->sql;

		if ($sql === '') {
			return '';
		}

		return substr($sql, 0, -2) . $this->sqlForEnd() . ';';
	}

	/**
	 * @param string[] $fields
	 * @return string[]
	 */
	protected function filterFieldsToUpdate(array $fields): array
	{
		return $fields;
	}

	private function sqlForStart(OperationValues $values): string
	{
		$this->required = $keys = $values->keys();

		return sprintf(
			'INSERT INTO %s (%s) VALUES ',
			$this->table,
			implode(', ', array_map($this->resolveField(...), $keys)),
		);
	}

	private function sqlForEnd(): string
	{
		if ($this->mode === self::ModeUpsert) {
			if ($this->columnsToUpdate) {
				$columns = $this->columnsToUpdate;
			} else {
				$columns = $this->filterFieldsToUpdate($this->required);
			}

			return sprintf(
				' ON DUPLICATE KEY UPDATE %s',
				implode(', ', array_map(function ($column): string {
					$escaped = $this->resolveField($column);

					return $escaped . ' = VALUES(' . $escaped . ')';
				}, $columns))
			);
		} else if ($this->mode === self::ModeInsertNonExisting) {
			$column = $this->required[0] ?? throw new LogicException('No columns provided');
			$escaped = $this->resolveField($column);

			return sprintf(' ON DUPLICATE KEY UPDATE %s = %s', $escaped, $escaped);
		}

		return '';
	}

	private function checkCorrectOrder(OperationValues $values): void
	{
		if (!$this->required) {
			return;
		}

		if ($values->count() !== count($this->required)) {
			throw new InvalidArgumentException('Data must have same length.');
		}

		if ($this->required !== $values->keys()) {
			throw new InvalidArgumentException('Data must have same order.');
		}
	}

	protected function buildValues(OperationValues $values): string
	{
		return sprintf(
			'(%s)',
			implode(', ', array_map(fn (mixed $value) => $this->escaper->escapeValue($value), $values->all())),
		);
	}

	protected function reset(): void
	{
		$this->sql = '';
		$this->required = [];
	}

	protected function mapFieldToColumn(string $field): string
	{
		return $field;
	}

	private function resolveField(string $field): string
	{
		return $this->escaper->escapeColumn($this->mapFieldToColumn($field));
	}

	/**
	 * @param non-empty-string $sql
	 */
	abstract protected function executeSql(string $sql): void;

}
