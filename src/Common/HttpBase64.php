<?php declare(strict_types = 1);

namespace Shredio\Core\Common;

use InvalidArgumentException;

final class HttpBase64
{

	public static function encode(string $input): string
	{
		return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public static function decode(string $input): string
	{
		$decode = base64_decode(strtr($input, '-_', '+/'), true);

		if ($decode === false) {
			throw new InvalidArgumentException('Base 64 decoding failed.');
		}

		return $decode;
	}

	/**
	 * @throws InvalidArgumentException
	 */
	public static function decodeOrNull(string $input): ?string
	{
		$decode = base64_decode(strtr($input, '-_', '+/'), true);

		return $decode === false ? null : $decode;
	}

}
