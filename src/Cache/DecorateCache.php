<?php declare(strict_types = 1);

namespace Shredio\Core\Cache;

use Psr\SimpleCache\CacheInterface;

abstract class DecorateCache implements CacheInterface
{

	public function __construct(
		protected readonly CacheInterface $cache,
	)
	{
	}

	/**
	 * @inheritDoc
	 */
	public function get(string $key, mixed $default = null): mixed
	{
		return $this->cache->get($key, $default);
	}

	/**
	 * @inheritDoc
	 */
	public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
	{
		return $this->cache->set($key, $value, $ttl);
	}

	/**
	 * @inheritDoc
	 */
	public function delete(string $key): bool
	{
		return $this->cache->delete($key);
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
		return $this->cache->getMultiple($keys, $default);
	}

	/**
	 * @param iterable<mixed> $values
	 * @inheritDoc
	 */
	public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
	{
		return $this->cache->setMultiple($values, $ttl);
	}

	/**
	 * @inheritDoc
	 */
	public function deleteMultiple(iterable $keys): bool
	{
		return $this->cache->deleteMultiple($keys);
	}

	/**
	 * @inheritDoc
	 */
	public function has(string $key): bool
	{
		return $this->cache->has($key);
	}

}
