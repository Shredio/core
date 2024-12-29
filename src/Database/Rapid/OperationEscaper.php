<?php declare(strict_types = 1);

namespace Shredio\Core\Database\Rapid;

interface OperationEscaper
{

	public function escapeValue(mixed $value): string;

	public function escapeColumn(string $column): string;

}
