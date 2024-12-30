<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Cache;

use LogicException;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;

final class SymfonyAdapterFactory
{

	public static function create(
		string $dsn,
		?string $cacheDir = null,
		?string $namespace = null,
		?MarshallerInterface $marshaller = null,
		bool $workerMode = false,
	): AdapterInterface
	{
		if (str_starts_with($dsn, 'filesystem:')) {
			$path = ltrim(substr($dsn, strlen('filesystem:')), '/');

			if (!$path) {
				throw new LogicException('The filesystem cache DSN must specify a valid path after "filesystem:".');
			}

			if (!$cacheDir) {
				throw new LogicException('The cache cache directory must be specified.');
			}

			$fullPath = sprintf('%s/%s', $cacheDir, $path);

			return new FilesystemAdapter($namespace ?? '', directory: $fullPath, marshaller: $marshaller);
		}

		if (str_starts_with($dsn, 'array:')) {
			return new ArrayAdapter();
		}

		if (str_starts_with($dsn, 'redis:') || str_starts_with($dsn, 'rediss:')) {
			return new RedisAdapter(RedisAdapter::createConnection($dsn, [
				'lazy' => !$workerMode,
			]), marshaller: $marshaller);
		}

		$pos = strpos($dsn, ':');

		if ($pos !== false) {
			$name = substr($dsn, 0, $pos);
		} else {
			$name = 'unknown';
		}

		throw new LogicException(sprintf('The "%s" is not supported yet.', $name));
	}

}
