<?php declare(strict_types = 1);

namespace Shredio\Core\Cache;

use Psr\SimpleCache\CacheInterface;

final class PrefixCache implements CacheInterface
{

	public function __construct(
		private readonly CacheInterface $cache,
		private readonly string $prefix,
	)
	{
	}

	/**
	 * @inheritDoc
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		return $this->cache->get($this->getKey($key), $default);
	}

	/**
	 * @inheritDoc
	 */
	public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
	{
		return $this->cache->set($this->getKey($key), $value, $ttl);
	}

	/**
	 * @inheritDoc
	 */
	public function delete(string $key): bool
	{
		return $this->cache->delete($this->getKey($key));
	}

	/**
	 * @inheritDoc
	 */
	public function clear(): bool
	{
		return $this->cache->clear();
	}

	/**
	 * @inheritDoc
	 */
	public function getMultiple(iterable $keys, mixed $default = null): iterable
	{
		return $this->cache->getMultiple($this->getKeys($keys), $default);
	}

	/**
	 * @param iterable<string, mixed> $values
	 * @inheritDoc
	 */
	public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
	{
		return $this->cache->setMultiple($this->getKeysWithValues($values), $ttl);
	}

	/**
	 * @inheritDoc
	 */
	public function deleteMultiple(iterable $keys): bool
	{
		return $this->cache->deleteMultiple($this->getKeys($keys));
	}

	/**
	 * @inheritDoc
	 */
	public function has(string $key): bool
	{
		return $this->cache->has($this->getKey($key));
	}

	protected function getKey(string $key): string
	{
		return $this->prefix . $key;
	}

	/**
	 * @param iterable<string> $keys
	 * @return iterable<string>
	 */
	private function getKeys(iterable $keys): iterable
	{
		foreach ($keys as $key) {
			yield $this->getKey($key);
		}
	}

	/**
	 * Generate a key-value pair iterable where the key is derived from each value.
	 *
	 * @param iterable<string, mixed> $values An iterable containing values to process.
	 * @return iterable<string> An iterable where keys are generated from values.
	 */
	private function getKeysWithValues(iterable $values): iterable
	{
		foreach ($values as $key => $value) {
			yield $this->getKey($key) => $value;
		}
	}

}
