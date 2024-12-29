<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Rest;

use RuntimeException;
use Shredio\Core\Rest\Locator\RestControllerLocator;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class RestLoader extends Loader
{

	private bool $isLoaded = false;

	private readonly SymfonyParameterInterpolation $interpolation;

	public function __construct(
		private KernelInterface $kernel,
		private RestControllerLocator $restControllerLocator,
	)
	{
		parent::__construct();

		$this->interpolation = new SymfonyParameterInterpolation();
	}

	public function load(mixed $resource, ?string $type = null): RouteCollection
	{
		if ($this->isLoaded) {
			throw new RuntimeException('Do not add the "rest" loader twice');
		}

		$this->isLoaded = true;

		if (!isset($resource['path'])) {
			throw new RuntimeException('The "path" key must be set');
		}

		$path = $resource['path'];

		if (!is_string($path)) {
			throw new RuntimeException('The "path" key must be a string');
		}

		$path = $this->kernel->getProjectDir() . '/' . ltrim($path, '/');

		if (!is_dir($path)) {
			throw new RuntimeException(sprintf('The directory "%s" does not exist', $path));
		}

		$routes = new RouteCollection();

		foreach ($this->restControllerLocator->locate($path) as $controller) {
			foreach ($controller->endpoints as $endpoint) {
				$routes->add($controller->getRouteName($endpoint), new Route(
					$endpoint->getPattern($controller)
						->toString($this->interpolation, $endpoint->getParameters($controller)),
					[
						'_controller' => $controller->className . '::' . $endpoint->name,
					],
					methods: $endpoint->attribute->getMethods(),
				));
			}
		}

		return $routes;
	}

	public function supports(mixed $resource, ?string $type = null): bool
	{
		return $type === 'rest';
	}

}
