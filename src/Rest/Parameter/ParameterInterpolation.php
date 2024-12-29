<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Parameter;

use Shredio\Core\Rest\Attribute\RouteParameter;

interface ParameterInterpolation
{

	/**
	 * @param array<string, RouteParameter> $parameters
	 */
	public function interpolate(string $route, array $parameters): string;

}
