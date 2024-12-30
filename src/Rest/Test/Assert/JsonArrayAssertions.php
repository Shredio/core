<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Test\Assert;

use PHPUnit\Framework\Assert;

class JsonArrayAssertions
{

	/**
	 * @param mixed[] $data
	 */
	public function __construct(
		protected readonly array $data,
	)
	{
	}

	/**
	 * @return mixed[]
	 */
	public function getData(): array
	{
		return $this->data;
	}

	public function getCount(): int
	{
		return count($this->data);
	}

	public function assertNotEmpty(): self
	{
		Assert::assertNotEmpty($this->data);

		return $this;
	}

	public function assertSame(string|int $key, mixed $expected): self
	{
		$this->run(fn (array $values) => Assert::assertSame($expected, $values[$key]));

		return $this;
	}

	public function assertEquals(string|int $key, mixed $expected): self
	{
		$this->run(fn (array $values) => Assert::assertEquals($expected, $values[$key]));

		return $this;
	}

	public function assertCount(int $expected): self
	{
		$this->run(fn (array $values) => Assert::assertCount($expected, $values));

		return $this;
	}

	/**
	 * @param string[] $keys
	 */
	public function assertHasKeys(array $keys): self
	{
		$this->run(function (array $values) use ($keys): void {
			foreach ($keys as $key) {
				Assert::assertArrayHasKey($key, $values);
			}
		});

		return $this;
	}

	/**
	 * @param string[] $keys
	 */
	public function assertSameKeys(array $keys): self
	{
		$this->assertCount(count($keys));
		$this->run(function (array $values) use ($keys): void {
			foreach ($keys as $key) {
				Assert::assertArrayHasKey($key, $values);
			}
		});

		return $this;
	}

	/**
	 * @param callable(mixed[] $values): void $fn
	 */
	protected function run(callable $fn): void
	{
		$fn($this->data);
	}

}
