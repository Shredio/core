<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Bundle\Compiler;

use Shredio\Core\Bridge\Doctrine\Type\AccountIdType;
use Shredio\Core\Bridge\Doctrine\Type\DateImmutablePrimaryType;
use Shredio\Core\Bridge\Doctrine\Type\SymbolType;
use Shredio\Messenger\Middleware\DiscardableMessageMiddleware;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

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
		$this->processMessenger($container);
	}

	private function processMessenger(ContainerBuilder $container): void
	{
		if (!class_exists(DiscardableMessageMiddleware::class)) {
			return;
		}

		$services = [
			'event.bus' => true,
			'query.bus' => true,
			'command.bus' => true,
		];

		foreach ($container->findTaggedServiceIds('messenger.bus') as $id => $tags) {
			if (!isset($services[$id])) {
				throw new RuntimeException(\sprintf('Invalid bus "%s": only "event.bus", "command.bus" and "query.bus" are allowed.', $id));
			}

			$container->getDefinition(sprintf('core.messenger.%s', $id))
				->setArgument('$bus', new Reference($id));

			unset($services[$id]);
		}

		foreach ($services as $id => $_) {
			$container->removeDefinition(sprintf('core.messenger.%s', $id));
		}

		foreach ($container->findTaggedServiceIds('messenger.receiver') as $id => $tags) {
			$receiverClass = $this->getServiceClass($container, $id);
			if (!is_subclass_of($receiverClass, ReceiverInterface::class)) {
				throw new RuntimeException(\sprintf('Invalid receiver "%s": class "%s" must implement interface "%s".', $id, $receiverClass, ReceiverInterface::class));
			}

			$receiverMapping[$id] = new Reference($id);

			foreach ($tags as $tag) {
				if (isset($tag['alias'])) {
					$receiverMapping[$tag['alias']] = $receiverMapping[$id];
				}
			}
		}

		$receiverNames = [];
		foreach ($receiverMapping as $name => $reference) {
			$receiverNames[(string) $reference] = $name;
		}

		$consumeCommandDefinition = $container->getDefinition('core.console.consume-cron-messages');

		if ($container->hasDefinition('messenger.routable_message_bus')) {
			$consumeCommandDefinition->replaceArgument(1, new Reference('messenger.routable_message_bus'));
		}

		$consumeCommandDefinition->replaceArgument(5, array_values($receiverNames));
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

	private function getServiceClass(ContainerBuilder $container, string $serviceId): string
	{
		while (true) {
			$definition = $container->findDefinition($serviceId);

			if (!$definition->getClass() && $definition instanceof ChildDefinition) {
				$serviceId = $definition->getParent();

				continue;
			}

			$class = $definition->getClass();

			if ($class === null) {
				throw new RuntimeException(sprintf('Service "%s" is missing a class.', $serviceId));
			}

			return $class;
		}
	}

}
