<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\DI;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final readonly class RepositoryRegister
{

	private ServicesConfigurator $services;

	private bool $stage;

	private bool $cache;

	public function __construct(ContainerConfigurator $container)
	{
		$this->stage = $container->env() === 'stage';
		$this->cache = strcasecmp($_ENV['CACHE_REPOSITORY'], 'true') === 0;
		$this->services = $container->services();
		$this->services->defaults()
			->autowire();
	}

	/**
	 * @param class-string $interface
	 * @param class-string $class
	 * @param class-string|null $stage
	 * @param class-string|null $cache
	 */
	public function register(string $interface, string $class, ?string $stage = null, ?string $cache = null): void
	{
		if ($this->stage && $stage) {
			$class = $stage;
		}

		$service = $this->services->set($class);

		if ($cache && $this->cache) {
			$service = $this->services->set($cache)
				->decorate($class)
				->args([service('.inner')]);
		}

		$service->alias($interface, $class);
	}

}
