<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Parameter;

use LogicException;
use Shredio\Core\Iterator\SimpleStringIterator;
use Shredio\Core\Rest\Attribute\RouteParameter;

trait ParameterInterpolationReplacement
{

	/**
	 * @param array<string, RouteParameter> $parameters
	 * @param callable(RouteParameter $parameter): string $fn
	 */
	private function replace(string $route, array $parameters, callable $fn): string
	{
		$interpolated = '';
		$iterator = new SimpleStringIterator($route);
		$getName = static function (SimpleStringIterator $iterator): ?string {
			$buffer = '';
			while ($current = $iterator->next()) {
				if ($current === '}') {
					return $buffer;
				}
				$buffer .= $current;
			}

			return null;
		};

		while ($current = $iterator->next()) {
			if ($current === '{') {
				$name = $getName($iterator);

				if ($name === null) {
					throw new LogicException(sprintf('Unclosed parameter in route %s', $route));
				}

				if ($name === '') {
					throw new LogicException(sprintf('Empty parameter name in route %s', $route));
				}

				if (!isset($parameters[$name])) {
					throw new LogicException(sprintf('Parameter %s not found in route %s', $name, $route));
				}

				$parameter = $parameters[$name];

				$interpolated .= $fn($parameter);
			} else {
				$interpolated .= $current;
			}
		}

		return $interpolated;
	}

}
