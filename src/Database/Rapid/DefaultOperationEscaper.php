<?php declare(strict_types = 1);

namespace Shredio\Core\Database\Rapid;

use DateTimeInterface;
use PDO;

final readonly class DefaultOperationEscaper implements OperationEscaper
{

	/** @var callable(mixed $value, int $type): string */
	private mixed $escape;

	/**
	 * @param callable(mixed $value, int $type): string $escape
	 */
	public function __construct(callable $escape)
	{
		$this->escape = $escape;
	}

	public function escapeValue(mixed $value): string
	{
		if ($value === null) {
			return 'NULL';
		}

		$type = $this->detectType($value);

		if ($value instanceof DateTimeInterface) {
			$value = $value->format('Y-m-d H:i:s');
		} else if (is_bool($value)) {
			$value = $value ? '1' : '0';
		} else {
			$value = (string) $value;
		}

		return ($this->escape)($value, $type);
	}

	public function escapeColumn(string $column): string
	{
		return sprintf('`%s`', trim($column, '`'));
	}

	private static function detectType(mixed $value): int
	{
		if (is_int($value)) {
			return PDO::PARAM_INT;
		}

		if (is_bool($value)) {
			return PDO::PARAM_BOOL;
		}

		if (is_null($value)) {
			return PDO::PARAM_NULL;
		}

		return PDO::PARAM_STR;
	}

}
