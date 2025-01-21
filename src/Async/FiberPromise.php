<?php declare(strict_types = 1);

namespace Shredio\Core\Async;

use Fiber;

/**
 * @template-covariant T
 * @implements Promise<T>
 */
final class FiberPromise implements Promise
{

	/**
	 * @phpstan-param Fiber<void, void, T, void> $fiber
	 * @psalm-param Fiber $fiber
	 * @param Fiber<void, void, T, void> $fiber
	 */
	public function __construct(
		private readonly Fiber $fiber,
	)
	{
	}

	/**
	 * @return T
	 */
	public function await(): mixed
	{
		while (!$this->fiber->isTerminated()) {
			$this->fiber->resume();
		}

		return $this->fiber->getReturn();
	}

	/**
	 * @template TRet
	 * @param callable(): TRet $fn
	 * @return self<TRet>
	 */
	public static function run(callable $fn): self
	{
		$fiber = new Fiber($fn);
		$fiber->start();

		return new self($fiber);
	}

	public static function wait(): void
	{
		if (Fiber::getCurrent() !== null) {
			Fiber::suspend();
		}
	}

}
