<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Bundle\Compiler;

use Shredio\Core\Bridge\Doctrine\Type\AccountIdType;
use Shredio\Core\Bridge\Doctrine\Type\DateImmutablePrimaryType;
use Shredio\Core\Bridge\Doctrine\Type\SymbolType;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class CoreCompilerPass implements CompilerPassInterface
{

	public function process(ContainerBuilder $container): void
	{
		// Cache Marshaller
		if (extension_loaded('igbinary')) {
			$container->getDefinition('cache.default_marshaller')
				->setArgument('$useIgbinarySerialize', true);
		}

		$this->processDoctrineTypes($container);
	}

	private function processDoctrineTypes(ContainerBuilder $container): void
	{
		if (!$container->hasParameter('doctrine.dbal.connection_factory.types')) {
			return;
		}

		/** @var mixed[] $typeDefinition */
		$typeDefinition = $container->getParameter('doctrine.dbal.connection_factory.types');

		if (!isset($typeDefinition[SymbolType::Name])) {
			$typeDefinition[SymbolType::Name] = ['class' => SymbolType::class];
		}

		if (!isset($typeDefinition[AccountIdType::Name])) {
			$typeDefinition[AccountIdType::Name] = ['class' => AccountIdType::class];
		}

		if (!isset($typeDefinition[DateImmutablePrimaryType::Name])) {
			$typeDefinition[DateImmutablePrimaryType::Name] = ['class' => DateImmutablePrimaryType::class];
		}

		$container->setParameter('doctrine.dbal.connection_factory.types', $typeDefinition);
	}

}
