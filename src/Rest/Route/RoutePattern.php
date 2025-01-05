<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Route;

use RuntimeException;
use Shredio\Core\Rest\Attribute\RouteParameter;
use Shredio\Core\Rest\Parameter\ParameterInterpolation;

final readonly class RoutePattern
{

	public string $route;

	public function __construct(
		string $route,
		private ?string $id = null,
	)
	{
		$this->route = '/' . trim($route, '/');
	}

	/**
	 * @return string[]
	 */
	public function getParameters(): array
	{
		if (!preg_match_all('/{([^}]+)}/', $this->route, $matches)) {
			return [];
		}

		return $matches[1];
	}

	public function withPrependedPattern(string $pattern): self
	{
		$pattern = trim($pattern, '/');

		return new self(
			$pattern . $this->route,
			$this->id,
		);
	}

	public function withAppendedPattern(string $pattern): self
	{
		$pattern = trim($pattern, '/');

		return new self(
			$this->route . '/' . $pattern,
			$this->id,
		);
	}

	public function getParametrizedPath(): string
	{
		if ($this->id) {
			return $this->route . '/' . sprintf('{%s}', $this->id);
		} else {
			return $this->route;
		}
	}

	public function createParametrizedRoute(): self
	{
		return new self($this->getParametrizedPath());
	}

	public static function camelCaseToKebabCase(string $str): string
	{
		$replaced = preg_replace('/([a-z])([A-Z])/', '$1-$2', $str);

		if ($replaced === null) {
			throw new RuntimeException('preg_replace failed');
		}

		return strtolower($replaced);
	}

	/**
	 * @param array<string, RouteParameter> $parameters
	 */
	public function toString(ParameterInterpolation $interpolation, array $parameters): string
	{
		return $interpolation->interpolate($this->route, $parameters);
	}

}
