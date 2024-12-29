<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Kernel;

use ReflectionObject;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

trait SlimKernel // @phpstan-ignore-line
{

	use MicroKernelTrait;

	private function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
	{
		$configDir = preg_replace('{/config$}', '/{config}', $this->getConfigDir());

		$container->import($configDir.'/{packages}/*.yaml');
		$container->import($configDir.'/{packages}/'.$this->environment.'/*.yaml');

		$container->import($configDir.'/services.yaml');
		$container->import($configDir.'/{services}_'.$this->environment.'.yaml');
	}

	private function configureRoutes(RoutingConfigurator $routes): void
	{
		$configDir = preg_replace('{/config$}', '/{config}', $this->getConfigDir());

		$routes->import($configDir.'/{routes}/'.$this->environment.'/*.yaml');
		$routes->import($configDir.'/{routes}/*.yaml');

		$routes->import($configDir.'/routes.yaml');

		if ($fileName = (new ReflectionObject($this))->getFileName()) {
			$routes->import($fileName, 'attribute');
		}
	}

}
