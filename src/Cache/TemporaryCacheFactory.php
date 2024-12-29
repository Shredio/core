<?php declare(strict_types = 1);

namespace Shredio\Core\Cache;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class TemporaryCacheFactory implements CacheFactory
{

	public function create(?string $name = null): Cache
	{
		return new ExtendCache(new Psr16Cache(new ArrayAdapter()));
	}

}
