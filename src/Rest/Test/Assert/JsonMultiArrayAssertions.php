<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Test\Assert;

use PHPUnit\Framework\Assert;

final class JsonMultiArrayAssertions extends JsonArrayAssertions
{

	private int|string|null $index = null;

	/**
	 * @param mixed[] $data
	 */
	public function __construct(array $data)
	{
		parent::__construct($data);
	}

	public function assertSame(int|string $key, mixed $expected, int|string|null $index = null): JsonArrayAssertions
	{
		$this->index = $index;

		return parent::assertSame($key, $expected);
	}

	public function assertEquals(int|string $key, mixed $expected, int|string|null $index = null): JsonArrayAssertions
	{
		$this->index = $index;

		return parent::assertEquals($key, $expected);
	}

	public function assertCount(int $expected, int|string|null $index = null): JsonArrayAssertions
	{
		$this->index = $index;

		return parent::assertCount($expected);
	}

	public function assertHasKeys(array $keys, int|string|null $index = null): JsonArrayAssertions
	{
		$this->index = $index;

		return parent::assertHasKeys($keys);
	}

	public function assertSameKeys(array $keys, int|string|null $index = null): JsonArrayAssertions
	{
		$this->index = $index;

		return parent::assertSameKeys($keys);
	}

	/**
	 * @param callable(mixed[] $values): void $fn
	 */
	protected function run(callable $fn): void
	{
		if ($this->index !== null) {
			$index = $this->index;
			$this->index = null;

			if (!isset($this->data[$index])) {
				Assert::assertArrayHasKey($index, $this->data);
			}

			$fn($this->data[$index]);

			return;
		}

		foreach ($this->data as $item) {
			$fn($item);
		}
	}

}
