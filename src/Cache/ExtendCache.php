<?php declare(strict_types = 1);

namespace Shredio\Core\Cache;

use DateInterval;
use DateTime;
use DateTimeInterface;

final class ExtendCache extends DecorateCache implements Cache
{

	/**
	 * @template TValue
	 * @param callable(): TValue $fn
	 * @return TValue
	 */
	public function grab(string $key, callable $fn, DateTimeInterface|DateInterval|int|null $ttl = null): mixed
	{
		$value = $this->cache->get($key);

		if ($value !== null) {
			return $value;
		}

		$value = $fn();

		if ($value === null) {
			return $value;
		}

		if ($ttl instanceof DateTimeInterface) {
			$ttl = (new DateTime())->diff($ttl);
		}

		$this->cache->set($key, $value, $ttl);

		return $value;
	}

	/**
	 * @template TValue
	 * @param callable(): TValue $fn
	 * @param callable(mixed): boolean $validator
	 * @return TValue
	 */
	public function grabTypeSafe(
		string $key,
		callable $fn,
		callable $validator,
		DateInterval|DateTimeInterface|int|null $ttl = null,
	): mixed
	{
		$value = $this->grab($key, $fn, $ttl);

		if (!$validator($value)) {
			trigger_error(
				sprintf('Invalid value in cache for key %s, value is of type %s', $key, $this->getTypeToDebug($value)),
			);

			$this->cache->delete($key);

			return $this->grab($key, $fn, $ttl);
		}

		return $value;
	}

	private function getTypeToDebug(mixed $value): string
	{
		if (is_string($value)) {
			$type = 'string(' . $this->truncateString($value, 50)  . ')';
		} else {
			$type = get_debug_type($value);
		}

		return $type;
	}

	private function truncateString(string $str, int $length): string
	{
		if (mb_strlen($str) <= $length) {
			return $str;
		}

		return mb_substr($str, 0, $length - 3) . '...';
	}

}