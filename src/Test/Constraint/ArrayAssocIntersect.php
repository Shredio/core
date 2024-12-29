<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Util\Exporter;

final class ArrayAssocIntersect extends Constraint
{

	/**
	 * @param iterable<mixed> $expected
	 */
	public function __construct(
		private iterable $expected,
	)
	{
	}

	protected function matches(mixed $other): bool
	{
		if (!is_iterable($other)) {
			return false;
		}

		return $this->check($this->expected, $other);
	}

	/**
	 * @param iterable<mixed> $expected
	 * @param iterable<mixed> $actual
	 */
	private function check(iterable $expected, iterable $actual): bool
	{
		$actual = $this->toArray($actual);

		foreach ($expected as $key => $value) {
			if (!array_key_exists($key, $actual)) {
				return false;
			}

			if (is_iterable($value)) {
				if (!is_iterable($actual[$key])) {
					return false;
				}

				if (!$this->check($value, $actual[$key])) {
					return false;
				}

				continue;
			}

			if ($value !== $actual[$key]) {
				return false;
			}
		}

		return true;
	}

	public function toString(): string
	{
		return 'has the assoc intersect ' . Exporter::export($this->toArray($this->expected));
	}

	/**
	 * @param iterable<mixed> $other
	 * @return mixed[]
	 */
	private function toArray(iterable $other): array
	{
		if (is_array($other)) {
			return $other;
		}

		return iterator_to_array($other);
	}

}
