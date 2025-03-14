<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Cache;

use OutOfBoundsException;
use Psr\SimpleCache\CacheInterface;
use Shredio\Core\Cache\Cache;
use Shredio\Core\Cache\CacheFactory;
use Shredio\Core\Cache\ExtendCache;
use Shredio\Core\Cache\SilentCache;
use Shredio\Core\Reporter\ExceptionReporter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;

final readonly class SymfonyCacheFactory implements CacheFactory
{

	/**
	 * @param array<string, CacheInterface> $caches
	 */
	public function __construct(
		private array $caches,
		private ExceptionReporter $exceptionReporter,
		private bool $scream = true,
		private bool $enabled = true,
	)
	{
	}

	public function create(?string $name = null): Cache
	{
		if (!$this->enabled) {
			if ($name && !isset($this->caches[$name])) {
				throw new OutOfBoundsException(sprintf('Cache "%s" not found.', $name));
			}

			return new ExtendCache(new Psr16Cache(new NullAdapter()));
		}

		$storage = $this->getStorage($name);

		if (!$this->scream) {
			$storage = new SilentCache($storage, $this->exceptionReporter);
		}

		return new ExtendCache($storage);
	}

	private function getStorage(?string $name = null): CacheInterface
	{
		if ($name === null) {
			$name = 'default';
		}

		return $this->caches[$name] ?? throw new OutOfBoundsException(sprintf('Cache "%s" not found.', $name));
	}

}
