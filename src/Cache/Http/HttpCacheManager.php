<?php declare(strict_types = 1);

namespace Shredio\Core\Cache\Http;

use InvalidArgumentException;

final class HttpCacheManager
{

	/**
	 * @param iterable<HttpCache> $services
	 */
	public function __construct(
		private iterable $services,
	)
	{
	}

	/**
	 * @template T of HttpCache
	 * @param class-string<T> $class
	 * @return T
	 */
	public function get(string $class): HttpCache
	{
		foreach ($this->services as $service) {
			if ($service instanceof $class) {
				return $service;
			}
		}

		throw new InvalidArgumentException(sprintf('Http cache %s not found', $class));
	}

}
