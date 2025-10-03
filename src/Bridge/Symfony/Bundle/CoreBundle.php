<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Bundle;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Shredio\Core\Bridge\Doctrine\EntityManagerRegistry;
use Shredio\Core\Bridge\Doctrine\Pagination\DoctrinePagination;
use Shredio\Core\Bridge\Doctrine\Query\DefaultQueryBuilderFactory;
use Shredio\Core\Bridge\Doctrine\Query\QueryBuilderFactory;
use Shredio\Core\Bridge\Doctrine\Repository\DoctrineRepositoryHelper;
use Shredio\Core\Bridge\Doctrine\Repository\DoctrineRepositoryServices;
use Shredio\Core\Bridge\Doctrine\TrackingPolicy\DeferredTrackingPolicy;
use Shredio\Core\Bridge\Symfony\Bundle\Compiler\CoreCompilerPass;
use Shredio\Core\Bridge\Symfony\Bundle\Compiler\PackagerCompilerPass;
use Shredio\Core\Bridge\Symfony\Cache\SymfonyCacheFactory;
use Shredio\Core\Bridge\Symfony\Environment\SymfonyAppEnvironment;
use Shredio\Core\Bridge\Symfony\Error\CustomProblemNormalizer;
use Shredio\Core\Bridge\Symfony\Error\ErrorListener;
use Shredio\Core\Bridge\Symfony\Error\ProblemNormalizer;
use Shredio\Core\Bridge\Symfony\Extension\SymfonyExtensionHelper;
use Shredio\Core\Bridge\Symfony\Http\PsrRequestResolver;
use Shredio\Core\Bridge\Symfony\Middleware\PackagingMiddleware;
use Shredio\Core\Bridge\Symfony\Pagination\SymfonyPaginationLinkGenerator;
use Shredio\Core\Bridge\Symfony\Reporter\SymfonyExceptionReporter;
use Shredio\Core\Bridge\Symfony\Rest\RestLoader;
use Shredio\Core\Bridge\Symfony\Rest\SymfonyRestOperationsFactory;
use Shredio\Core\Bridge\Symfony\Security\SymfonyAuthenticator;
use Shredio\Core\Bridge\Symfony\Serializer\AccountIdNormalizer;
use Shredio\Core\Bridge\Symfony\Serializer\KeepObjectNormalizer;
use Shredio\Core\Bridge\Symfony\Serializer\SymbolNormalizer;
use Shredio\Core\Bridge\Symfony\Test\ErrorHandlerForTests;
use Shredio\Core\Bridge\Symfony\Test\SchemaMiddleware;
use Shredio\Core\Bridge\Symfony\Test\TestBench;
use Shredio\Core\Cache\CacheFactory;
use Shredio\Core\Cache\Http\HttpCache;
use Shredio\Core\Cache\Http\HttpCacheManager;
use Shredio\Core\Cache\PrefixCache;
use Shredio\Core\Command\AuthTokenCommand;
use Shredio\Core\Command\DecodeTokenCommand;
use Shredio\Core\Command\EncodeTokenCommand;
use Shredio\Core\Entity\EntityFactory;
use Shredio\Core\Entity\Metadata\ContextExtractor;
use Shredio\Core\Entity\SymfonyEntityFactory;
use Shredio\Core\Environment\AppEnvironment;
use Shredio\Core\Format\Formatter\BigMoneyFormatter;
use Shredio\Core\Format\Formatter\DaysFormatter;
use Shredio\Core\Format\Formatter\DecimalFormatter;
use Shredio\Core\Format\Formatter\MoneyFormatter;
use Shredio\Core\Format\Formatter\PercentFormatter;
use Shredio\Core\Format\Formatter\Service\TimeAgoInDays;
use Shredio\Core\Format\Formatter\TimeAgoInDaysFormatter;
use Shredio\Core\Format\FormatterRegistry;
use Shredio\Core\Format\ValuesFormatter;
use Shredio\Core\Package\Packager;
use Shredio\Core\Package\Processor\FormattingInstructionProcessor;
use Shredio\Core\Package\Processor\InstructionProcessor;
use Shredio\Core\Package\Processor\SerializationInstructionProcessor;
use Shredio\Core\Pagination\ArrayPagination;
use Shredio\Core\Pagination\ChainablePagination;
use Shredio\Core\Pagination\Pagination;
use Shredio\Core\Pagination\PaginationChain;
use Shredio\Core\Pagination\PaginationLinkGenerator;
use Shredio\Core\Path\Directories;
use Shredio\Core\Payload\ErrorsPayloadProcessor;
use Shredio\Core\Reporter\ExceptionReporter;
use Shredio\Core\Rest\Locator\RestControllerLocator;
use Shredio\Core\Rest\Metadata\ControllerMetadataFactory;
use Shredio\Core\Rest\Metadata\DefaultControllerMetadataFactory;
use Shredio\Core\Rest\Metadata\DefaultEndpointMetadataFactory;
use Shredio\Core\Rest\Metadata\EndpointMetadataFactory;
use Shredio\Core\Rest\RestOperationsFactory;
use Shredio\Core\Security\AuthTokenProvider;
use Shredio\Core\Security\PasetoProvider;
use Shredio\Core\Security\TokenProvider;
use Shredio\Core\Serializer\Argument\ObjectNormalizerServices;
use Shredio\Messenger\Bus\CommandBus;
use Shredio\Messenger\Bus\DefaultMessengerBusAccessor;
use Shredio\Messenger\Bus\EventBus;
use Shredio\Messenger\Bus\QueryBus;
use Shredio\Messenger\Command\ConsumeCronMessagesCommand;
use Shredio\Messenger\Middleware\DiscardableMessageMiddleware;
use Shredio\RapidDatabaseOperations\Doctrine\DoctrineRapidOperationFactory;
use Shredio\RapidDatabaseOperations\RapidOperationFactory;
use Symfony\Bridge\PsrHttpMessage\EventListener\PsrResponseListener;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Serializer\Serializer;
use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

final class CoreBundle extends AbstractBundle
{

	use SymfonyExtensionHelper;

	/**
	 * @param mixed[] $config
	 */
	public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		if ($container->env() === 'test') {
			$this->loadTest($container, $builder);
		}

		$container->parameters()->set('core.api', $isApi = $config['api'] ?? false);

		if ($config['extensions']['authentication'] ?? false) {
			$this->loadAuthentication($container, $builder);
		}

		$this->loadRestRouter($container, $builder);
		$this->loadBasics($container, $builder);
		$this->loadCache($container, $builder, $config['cache'] ?? []);
		$this->loadPsr7($container, $builder);
		$this->loadDoctrine($container, $builder);
		$this->loadPackager($container, $builder);
		$this->loadMiddlewares($container, $builder);
		$this->loadSerializer($container, $builder);
		$this->loadHttpCache($container, $builder);
		$this->loadMessenger($container, $builder);

		if ($isApi) {
			$this->loadApi($container, $builder);
		}
	}

	private function loadBasics(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();

		$this->addInterfaceService($services, AppEnvironment::class, SymfonyAppEnvironment::class);
		$this->addInterfaceService($services, ExceptionReporter::class, SymfonyExceptionReporter::class);

		$services->set(ErrorListener::class)
			->autowire()
			->autoconfigure();

		$services->set(ErrorsPayloadProcessor::class)->autowire();

		$services->set('directories')
			->autowire()
			->synthetic()
			->alias(Directories::class, 'directories');
	}

	private function loadRestRouter(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();

		$services->set(RestLoader::class)
			->autowire()
			->autoconfigure()
			->tag('routing.loader');

		$services->set(RestControllerLocator::class)->autowire();

		$this->addInterfaceService($services, ControllerMetadataFactory::class, DefaultControllerMetadataFactory::class);
		$this->addInterfaceService($services, EndpointMetadataFactory::class, DefaultEndpointMetadataFactory::class);
		$this->addInterfaceService($services, RestOperationsFactory::class, SymfonyRestOperationsFactory::class);
	}

	public function build(ContainerBuilder $container): void
	{
		parent::build($container);

		$container->addCompilerPass(new CoreCompilerPass());
		$container->addCompilerPass(new PackagerCompilerPass());
	}

	public function configure(DefinitionConfigurator $definition): void
	{
		$definition->rootNode() // @phpstan-ignore-line
			->children()
				->booleanNode('api')->defaultFalse()->end()
				->arrayNode('extensions')
					->children()
						->booleanNode('authentication')->defaultFalse()->end()
					->end()
				->end()
				->arrayNode('cache')
					->children()
						->booleanNode('enabled')->defaultTrue()->end()
						->arrayNode('aliases')
							->useAttributeAsKey('name')
							->normalizeKeys(false)
							->arrayPrototype()
								->children()
									->stringNode('prefix')->cannotBeEmpty()->end()
									->stringNode('cache')->end()
								->end()
							->end()
						->end() // aliases
					->end()
				->end() // cache
				->arrayNode('middlewares')
					->stringPrototype()
					->end()
				->end()
			->end();
	}

	/**
	 * @param mixed[] $config
	 */
	private function loadCache(ContainerConfigurator $container, ContainerBuilder $builder, array $config): void
	{
		$services = $container->services();
		$services->defaults()
			->autoconfigure()
			->autowire();

		$aliases = $config['aliases'] ?? [];

		if (!isset($aliases['default'])) {
			$aliases['default'] = [
				'prefix' => '',
			];
		}

		$collection = [];

		foreach ($aliases as $name => $settings) {
			$cache = new ReferenceConfigurator($settings['cache'] ?? 'cache.app');
			$serviceName = 'cache.storages.' . $name;

			$services->set($serviceName, CacheInterface::class)
				->factory([self::class, 'createCache'])
				->args([$cache, $settings['prefix']]);

			$collection[$name] = new ReferenceConfigurator($serviceName);
		}

		$services->set(SymfonyCacheFactory::class)
			->args([$collection])
			->arg('$scream', param('kernel.debug'))
			->arg('$enabled', $config['enabled'] ?? true)
			->alias(CacheFactory::class, SymfonyCacheFactory::class);
	}

	public static function createCache(CacheItemPoolInterface $cache, string $prefix): PrefixCache
	{
		return new PrefixCache(new Psr16Cache($cache), $prefix);
	}

	private function loadAuthentication(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();

		$services->set('core.authenticator', SymfonyAuthenticator::class)
			->autowire()
			->autoconfigure();

		$this->addInterfaceService($services, TokenProvider::class, PasetoProvider::class)
			->arg('$secret', param('env(string:AUTH_PASETO_SECRET)'));

		$provider = $services->set('core.authTokenProvider', AuthTokenProvider::class)
			->autowire();

		if ($container->env() === 'test') {
			$provider->public();
		}

		$provider->alias(AuthTokenProvider::class, 'core.authTokenProvider');

		if (class_exists(Command::class)) {
			$this->addConsoleCommand($services, EncodeTokenCommand::class);
			$this->addConsoleCommand($services, DecodeTokenCommand::class);
			$this->addConsoleCommand($services, AuthTokenCommand::class);
		}
	}

	private function loadPsr7(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		if (!class_exists(PsrHttpFactory::class)) {
			return;
		}

		$services = $container->services();

		$services->set(PsrRequestResolver::class)
			->autowire()
			->autoconfigure()
			->tag('controller.argument_value_resolver', ['priority' => -100]);
		$services->set(PsrResponseListener::class)
			->autowire()
			->autoconfigure();

		$this->addInterfaceService($services, HttpFoundationFactoryInterface::class, HttpFoundationFactory::class);
		$this->addInterfaceService($services, HttpMessageFactoryInterface::class, PsrHttpFactory::class);
	}

	private function loadDoctrine(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();

		$services->set(DeferredTrackingPolicy::class)->autowire()->autoconfigure();
		$services->set(DoctrineRepositoryServices::class)->autowire();
		$services->set(DoctrineRepositoryHelper::class)->autowire();
		$services->set(EntityManagerRegistry::class)->autowire();
		$services->set(ContextExtractor::class)->autowire();

		$this->addInterfaceService($services, RapidOperationFactory::class, DoctrineRapidOperationFactory::class);
		$this->addInterfaceService($services, QueryBuilderFactory::class, DefaultQueryBuilderFactory::class);
		$this->addInterfaceService($services, EntityFactory::class, SymfonyEntityFactory::class);
		$this->addInterfaceService($services, PaginationLinkGenerator::class, SymfonyPaginationLinkGenerator::class);
		$this->addInterfaceService($services, Pagination::class, PaginationChain::class, name: 'core.pagination');

		$builder->registerForAutoconfiguration(ChainablePagination::class)
			->addTag('pagination.chain');

		$services->set(DoctrinePagination::class)
			->autowire()
			->tag('pagination.chain');
		$services->set(ArrayPagination::class)
			->autowire()
			->tag('pagination.chain');
	}

	private function loadPackager(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();

		$services->set('packager', Packager::class)
			->autowire()
			->alias(Packager::class, 'packager')
			->public();

		if (class_exists(Serializer::class)) {
			$this->addInterfaceService($services, InstructionProcessor::class, SerializationInstructionProcessor::class)
				->tag('packager.processor');
		}

		$this->addInterfaceService($services, InstructionProcessor::class, FormattingInstructionProcessor::class)
			->tag('packager.processor');

		$formatters = [
			TimeAgoInDaysFormatter::class,
			DecimalFormatter::class,
			PercentFormatter::class,
			MoneyFormatter::class,
			BigMoneyFormatter::class,
			DaysFormatter::class,
		];

		$services->set(TimeAgoInDays::class);

		$formatterServices = [];

		foreach ($formatters as $formatter) {
			$services->set($formatter)
				->autowire();

			$formatterServices[] = service($formatter);
		}

		$services->set(FormatterRegistry::class)
			->args([$formatterServices]);

		$services->set(ValuesFormatter::class)
			->autowire();
	}

	private function loadMiddlewares(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();

		if (interface_exists(HttpMessageFactoryInterface::class)) {
			$services->set(PackagingMiddleware::class)
				->autowire()
				->autoconfigure();
		}
	}

	private function loadSerializer(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();

		$services->set(SymbolNormalizer::class)
			->autowire()
			->tag('serializer.normalizer');

		$services->set(AccountIdNormalizer::class)
			->autowire()
			->tag('serializer.normalizer');

		$services->set(KeepObjectNormalizer::class)
			->autowire()
			->tag('serializer.normalizer');

		$services->set(ObjectNormalizerServices::class)
			->autowire();
	}

	private function loadTest(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();
		$services->set(ErrorHandlerForTests::class)
			->args([service('.inner')])
			->decorate('error_handler.error_renderer.serializer');

		$services->set('testbench', TestBench::class)
			->autowire()
			->autoconfigure()
			->public();

		$services->set(SchemaMiddleware::class)
			->autoconfigure()
			->autowire()
			->tag('doctrine.middleware', ['priority' => 110]);
	}

	private function loadHttpCache(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$builder->registerForAutoconfiguration(HttpCache::class)
			->addTag('core.http_cache');

		$services = $container->services();

		$services->set(HttpCacheManager::class)
			->args([tagged_iterator('core.http_cache')]);
	}

	private function loadMessenger(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();

		if (!class_exists(DiscardableMessageMiddleware::class)) {
			return;
		}

		$services->set(DiscardableMessageMiddleware::class)
			->autowire();

		$services->set('core.messenger.command.bus', CommandBus::class)
			->args([abstract_arg('Command bus')])
			->alias(CommandBus::class, 'core.messenger.command.bus');

		$services->set('core.messenger.query.bus', QueryBus::class)
			->args([abstract_arg('Query bus')])
			->alias(QueryBus::class, 'core.messenger.query.bus');

		$services->set('core.messenger.event.bus', EventBus::class)
			->args([abstract_arg('Event bus')])
			->alias(EventBus::class, 'core.messenger.event.bus');

		$services->set(DefaultMessengerBusAccessor::class)
			->args([
				service('core.messenger.command.bus')->nullOnInvalid(),
				service('core.messenger.query.bus')->nullOnInvalid(),
				service('core.messenger.event.bus')->nullOnInvalid(),
			]);

		$services->set('core.console.consume-cron-messages', ConsumeCronMessagesCommand::class)
			->tag('console.command')
			->args([
				service('messenger.receiver_locator'),
				abstract_arg('Routable message bus'),
				service('event_dispatcher'),
				service('logger')->nullOnInvalid(),
				service('messenger.rate_limiter_locator')->nullOnInvalid(),
				[], // Receiver names
			]);
	}

	private function loadApi(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();

		// removes type and title from the problem response
		$services->set(ProblemNormalizer::class)
			->decorate('serializer.normalizer.problem')
			->args([service('.inner'), tagged_iterator('core.problem_normalizer')]);

		$builder->registerForAutoconfiguration(CustomProblemNormalizer::class)
			->addTag('core.problem_normalizer');
	}

}
