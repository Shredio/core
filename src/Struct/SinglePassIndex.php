<?php declare(strict_types = 1);

namespace Shredio\Core\Struct;

use LogicException;

final class SinglePassIndex
{

	/**
	 * @param array<array-key, boolean> $index
	 */
	public function __construct(
		private array $index,
	)
	{
	}

	public function has(string|int $key): bool
	{
		if (isset($this->index[$key])) {
			unset($this->index[$key]);

			return true;
		}

		return false;
	}

	/**
	 * @param iterable<string|int> $values
	 */
	public static function fromValues(iterable $values): self
	{
		$index = [];

		foreach ($values as $value) {
			$index[$value] = true;
		}

		return new self($index);
	}

	public function report(): void
	{
		if (count($this->index) > 0) {
			throw new LogicException(sprintf('The following keys were not found: %s', implode(', ', array_keys($this->index))));
		}

		$this->index = [];
	}

}
