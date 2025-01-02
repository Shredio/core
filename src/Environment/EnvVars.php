<?php declare(strict_types = 1);

namespace Shredio\Core\Environment;

use RuntimeException;

final class EnvVars
{

	public static function getBoolean(string $name, bool $ifNotSet = false): bool
	{
		$value = self::get($name);

		if ($value === null) {
			return $ifNotSet;
		}

		$value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

		if ($value === null) {
			throw new RuntimeException(sprintf('Invalid boolean value for environment variable %s', $name));
		}

		return $value;
	}

	public static function getString(string $name, string $ifNotSet = ''): string
	{
		$value = self::get($name);

		if ($value === null) {
			return $ifNotSet;
		}

		return $value;
	}

	public static function require(string $name, string $description): void
	{
		if (self::get($name) === null) {
			throw new RuntimeException(sprintf('Missing required environment variable %s: %s', $name, $description));
		}
	}

	private static function get(string $name): ?string
	{
		$value = getenv($name);

		if ($value === false) {
			return $_ENV[$name] ?? null;
		}

		return $value;
	}

}
