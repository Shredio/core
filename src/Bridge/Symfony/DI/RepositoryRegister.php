<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\DI;

use Shredio\Core\Bridge\Symfony\Environment\SymfonyAppEnvironment;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final readonly class RepositoryRegister
{

	private ServicesConfigurator $services;

	private bool $stage;

	public function __construct(ContainerConfigurator $container)
	{
		$this->stage = SymfonyAppEnvironment::createFromEnv()->isStaging();
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

		if ($cache && !$this->stage) {
			$this->services->set($class);

			$this->services->set($interface, $cache)
				->args([service($class)]);
		} else {
			$this->services->set($interface, $class);
		}
	}

}
