<?php declare(strict_types = 1);

namespace Shredio\Core\Database\Rapid;

interface RapidOperation
{

	/**
	 * @param array<string, mixed> $values
	 */
	public function addRaw(array $values): static;

	public function add(OperationValues $values): static;

	public function execute(): void;

	public function getSql(): string;

}
