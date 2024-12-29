<?php declare(strict_types = 1);

namespace Shredio\Core\Cache;

use DateInterval;
use Psr\SimpleCache\CacheInterface;
use Shredio\Core\Reporter\ExceptionReporter;
use Throwable;

final class SilentCache implements CacheInterface
{

	public function __construct(
		private readonly CacheInterface $cache,
		private readonly ExceptionReporter $exceptionReporter,
	) {}

	public function get(string $key, mixed $default = null): mixed
	{
		try {
			return $this->cache->get($key, $default);
		} catch (Throwable $exception) {
			$this->exceptionReporter->report($exception);
		}

		return $default;
	}

	public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
	{
		try {
			return $this->cache->set($key, $value, $ttl);
		} catch (Throwable $exception) {
			$this->exceptionReporter->report($exception);
		}

		return false;
	}

	public function delete(string $key): bool
	{
		try {
			return $this->cache->delete($key);
		} catch (Throwable $exception) {
			$this->exceptionReporter->report($exception);
		}

		return false;
	}

	public function clear(): bool
	{
		try {
			return $this->cache->clear();
		} catch (Throwable $exception) {
			$this->exceptionReporter->report($exception);
		}

		return false;
	}

	public function getMultiple(iterable $keys, mixed $default = null): iterable
	{
		try {
			return $this->cache->getMultiple($keys, $default);
		} catch (Throwable $exception) {
			$this->exceptionReporter->report($exception);
		}

		return [];
	}

	/**
	 * @param iterable<mixed> $values
	 */
	public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool
	{
		try {
			return $this->cache->setMultiple($values, $ttl);
		} catch (Throwable $exception) {
			$this->exceptionReporter->report($exception);
		}

		return false;
	}

	public function deleteMultiple(iterable $keys): bool
	{
		try {
			return $this->cache->deleteMultiple($keys);
		} catch (Throwable $exception) {
			$this->exceptionReporter->report($exception);
		}

		return false;
	}

	public function has(string $key): bool
	{
		try {
			return $this->cache->has($key);
		} catch (Throwable $exception) {
			$this->exceptionReporter->report($exception);
		}

		return false;
	}

}
