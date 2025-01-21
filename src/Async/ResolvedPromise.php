<?php declare(strict_types = 1);

namespace Shredio\Core\Async;

/**
 * @template T
 * @implements Promise<T>
 */
final readonly class ResolvedPromise implements Promise
{

	/**
	 * @param T $value
	 */
	public function __construct(
		private mixed $value,
	)
	{
	}

	/**
	 * @return T
	 */
	public function await(): mixed
	{
		return $this->value;
	}

}
