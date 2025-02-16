<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Doctrine\Result;

use BackedEnum;
use Doctrine\ORM\Query;
use Shredio\Core\Common\Trait\DisallowSerialization;
use Shredio\Core\Struct\Map;
use Stringable;

final readonly class DatabasePairsResult
{

	use DisallowSerialization;

	public function __construct(
		private Query $query,
		private string $key,
		private string $value,
	)
	{
	}

	/**
	 * @return mixed[] $key => $value
	 */
	public function toArray(): array
	{
		$values = [];

		foreach ($this->yield() as $item) {
			$values[$this->stringify($item[$this->key])] = $item[$this->value];
		}

		return $values;
	}

	/**
	 * @return mixed[] $key => $value
	 */
	public function toScalarArray(): array
	{
		$values = [];

		foreach ($this->yieldScalar() as $item) {
			$values[$item[$this->key]] = $item[$this->value];
		}

		return $values;
	}

	/**
	 * @return mixed[]
	 */
	public function yield(): iterable
	{
		return $this->query->toIterable(hydrationMode: Query::HYDRATE_ARRAY);
	}

	/**
	 * @return mixed[]
	 */
	public function yieldScalar(): iterable
	{
		return $this->query->toIterable(hydrationMode: Query::HYDRATE_SCALAR);
	}

	/**
	 * @return Map<string, mixed>
	 */
	public function createStringMap(): Map
	{
		$values = Map::createString();

		foreach ($this->yield() as $item) {
			$values->set($item[$this->key], $item[$this->value]);
		}

		return $values;
	}

	private function stringify(mixed $key): string
	{
		if (is_scalar($key)) {
			return (string) $key;
		}

		if ($key instanceof BackedEnum) {
			return (string) $key->value;
		}

		return (string) $key;
	}

}
