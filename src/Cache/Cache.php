<?php declare(strict_types = 1);

namespace Shredio\Core\Cache;

use DateInterval;
use DateTimeInterface;
use Psr\SimpleCache\CacheInterface;

interface Cache extends CacheInterface
{

	/**
	 * @template TValue
	 * @param callable(): TValue $fn
	 * @return TValue
	 */
	public function fallback(string $key, callable $fn, DateTimeInterface|DateInterval|int|null $ttl = null): mixed;

	/**
	 * @template TValue
	 * @param callable(): TValue $fn
	 * @param callable(mixed): boolean $validator
	 * @return TValue
	 */
	public function validate(string $key, callable $fn, callable $validator, DateTimeInterface|DateInterval|int|null $ttl = null): mixed;

}
