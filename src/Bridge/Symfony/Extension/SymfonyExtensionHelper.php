<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Extension;

use Symfony\Component\DependencyInjection\Loader\Configurator\ServiceConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;

trait SymfonyExtensionHelper
{

	/**
	 * @param class-string $interface
	 * @param class-string $class
	 */
	private function addInterfaceService(
		ServicesConfigurator $services,
		string $interface,
		string $class,
		bool $configure = false,
		?string $name = null,
	): ServiceConfigurator
	{
		if ($name === null) {
			$service = $services->set($class);
		} else {
			$service = $services->set($name, $class);
			$class = $name;
		}

		$service->autowire()
			->autoconfigure($configure)
			->alias($interface, $class);

		return $service;
	}

}
