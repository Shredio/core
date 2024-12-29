<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Cache;

use Closure;
use DateInterval;
use DateTimeInterface;
use Psr\Cache\CacheItemPoolInterface;
use Shredio\Core\Cache\Cache;
use Symfony\Component\Cache\CacheItem;

final class SymfonyCache implements Cache
{

	/** @var Closure(string $key, mixed $value, bool $isHit): CacheItem */
	private static Closure $createCacheItem;

	public function __construct(
		private readonly CacheItemPoolInterface $cache,
		private readonly ?string $prefix = null,
	)
	{
		self::$createCacheItem ??= Closure::bind(
			static function (string $key, mixed $value, bool $isHit): CacheItem {
				$item = new CacheItem();
				$item->key = $key;
				$item->value = $value;
				$item->isHit = $isHit;
				$item->unpack();

				return $item;
			},
			null,
			CacheItem::class
		);
	}

	/**
	 * @template TValue
	 * @param callable(): TValue $fn
	 * @return TValue
	 */
	public function grab(string $key, callable $fn, DateInterval|DateTimeInterface|int|null $ttl = null): mixed
	{
		$item = $this->cache->getItem($this->getKey($key));

		if ($item->isHit()) {
			return $item->get();
		}

		$value = $fn();

		if ($value === null) {
			return $value;
		}

		$item->set($value);

		if ($ttl instanceof DateTimeInterface) {
			$item->expiresAt($ttl);
		} else {
			$item->expiresAfter($ttl);
		}

		$this->cache->save($item);

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
		DateInterval|DateTimeInterface|int|null $ttl = null
	): mixed
	{
		$value = $this->grab($key, $fn, $ttl);

		if (!$validator($value)) {
			trigger_error(
				sprintf('Invalid value in cache for key %s, value is of type %s', $key, $this->getTypeToDebug($value)),
			);

			$this->delete($key);

			return $this->grab($key, $fn, $ttl);
		}

		return $value;
	}

	public function get(string $key, mixed $default = null): mixed
	{
		$item = $this->cache->getItem($this->getKey($key));

		if (!$item->isHit()) {
			return $default;
		}

		return $item->get();
	}

	public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
	{
		return $this->cache->save((self::$createCacheItem)($this->getKey($key), $value, true));
	}

	public function delete(string $key): bool
	{
		return $this->cache->deleteItem($this->getKey($key));
	}

	public function clear(): bool
	{
		return $this->cache->clear();
	}

	public function getMultiple(iterable $keys, mixed $default = null): iterable
	{
		foreach ($this->cache->getItems($this->getKeys($keys)) as $key => $item) {
			yield $key => $item->isHit() ? $item->get() : $default;
		}
	}

	/**
	 * @param iterable<mixed> $values
	 */
	public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool
	{
		foreach ($values as $key => $value) {
			$this->cache->saveDeferred((self::$createCacheItem)($this->get($key), $value, true));
		}

		return $this->cache->commit();
	}

	public function deleteMultiple(iterable $keys): bool
	{
		return $this->cache->deleteItems($this->getKeys($keys));
	}

	public function has(string $key): bool
	{
		return $this->cache->hasItem($this->getKey($key));
	}

	private function getKey(string $key): string
	{
		if ($this->prefix) {
			return $this->prefix . $key;
		}

		return $key;
	}

	/**
	 * @param iterable<string> $keys
	 * @return string[]
	 */
	private function getKeys(iterable $keys): array
	{
		$resolvedKeys = [];

		foreach ($keys as $key) {
			$resolvedKeys[] = $this->getKey($key);
		}

		return $resolvedKeys;
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
