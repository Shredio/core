<?php declare(strict_types = 1);

namespace Shredio\Core\Cache;

interface CacheFactory
{

	public function create(?string $name = null): Cache;

}
