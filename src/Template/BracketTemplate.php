<?php declare(strict_types = 1);

namespace Shredio\Core\Template;

final class BracketTemplate
{

	/**
	 * @param mixed[] $parameters
	 */
	public static function render(string $template, array $parameters): string
	{
		$keys = array_map(fn($key) => '{' . $key . '}', array_keys($parameters));
		$values = array_values($parameters);

		return str_replace($keys, $values, $template);
	}

}
