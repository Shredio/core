<?php declare(strict_types = 1);

namespace Shredio\Core\Common\Debug;

final class DebugHelper
{

	public static function stringifyMixed(mixed $value): string
	{
		if ($value === null) {
			return 'null';
		}

		if (is_bool($value)) {
			return $value ? 'true' : 'false';
		}

		if (is_string($value)) {
			if (mb_strlen($value) > 100) {
				return sprintf('"%s..."', mb_substr($value, 0, 100));
			}

			return sprintf('"%s"', $value);
		}

		if (is_numeric($value)) {
			return (string) $value;
		}

		if (is_array($value)) {
			return sprintf('array(%d)', count($value));
		}

		if (is_iterable($value)) {
			return sprintf('iterable(%d)', iterator_count($value));
		}

		return get_debug_type($value);
	}

}
