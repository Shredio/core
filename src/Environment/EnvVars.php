<?php declare(strict_types = 1);

namespace Shredio\Core\Environment;

use RuntimeException;

final class EnvVars
{

	public static function getBoolean(string $name, bool $ifNotSet = false): bool
	{
		if (!isset($_ENV[$name])) {
			return $ifNotSet;
		}

		$value = filter_var($_ENV[$name], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

		if ($value === null) {
			throw new RuntimeException(sprintf('Invalid boolean value for environment variable %s', $name));
		}

		return $value;
	}

	public static function getString(string $name, string $ifNotSet = ''): string
	{
		if (!isset($_ENV[$name])) {
			return $ifNotSet;
		}

		return $_ENV[$name];
	}

	public static function require(string $name, string $description): void
	{
		if (!isset($_ENV[$name])) {
			throw new RuntimeException(sprintf('Missing required environment variable %s: %s', $name, $description));
		}
	}

}
