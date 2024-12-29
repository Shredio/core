<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Bundle\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class PackagerCompilerPass implements CompilerPassInterface
{

	public function process(ContainerBuilder $container): void
	{
		$services = $container->findTaggedServiceIds('packager.processor');
		$packager = $container->getDefinition('packager');

		foreach ($services as $service => $tags) {
			$packager->addMethodCall('addProcessor', [new Reference($service)]);
		}
	}

}
