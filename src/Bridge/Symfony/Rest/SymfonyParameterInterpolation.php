<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Rest;

use InvalidArgumentException;
use Shredio\Core\Rest\Attribute\NamedPattern;
use Shredio\Core\Rest\Attribute\RouteParameter;
use Shredio\Core\Rest\Parameter\ParameterInterpolation;
use Shredio\Core\Rest\Parameter\ParameterInterpolationReplacement;

final class SymfonyParameterInterpolation implements ParameterInterpolation
{

	use ParameterInterpolationReplacement;

	/**
	 * @param array<string, RouteParameter> $parameters
	 */
	public function interpolate(string $route, array $parameters): string
	{
		return $this->replace($route, $parameters, static function (RouteParameter $parameter): string {
			static $patterns = [
				'uuid' => '[0-9a-fA-F]{8}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{4}\b-[0-9a-fA-F]{12}',
			];
			$interpolated = $parameter->name;

			if ($pattern = $parameter->pattern) {
				if ($pattern instanceof NamedPattern) {
					if (!isset($patterns[$pattern->name])) {
						throw new InvalidArgumentException(sprintf('Unknown named pattern: %s', $pattern->name));
					}

					$pattern = $patterns[$pattern->name];
				}

				$interpolated = sprintf('%s<%s>', $interpolated, $pattern);
			}

			if ($parameter->default !== false) {
				$interpolated = sprintf('%s?%s', $interpolated, $parameter->default);
			}

			return sprintf('{%s}', $interpolated);
		});
	}

}
