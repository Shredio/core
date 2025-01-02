<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Bundle;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Shredio\Core\Bridge\Doctrine\EntityManagerRegistry;
use Shredio\Core\Bridge\Doctrine\Pagination\DoctrinePaginationFactory;
use Shredio\Core\Bridge\Doctrine\Query\DefaultQueryBuilderFactory;
use Shredio\Core\Bridge\Doctrine\Query\QueryBuilderFactory;
use Shredio\Core\Bridge\Doctrine\Rapid\DoctrineEntityRapidOperationFactory;
use Shredio\Core\Bridge\Doctrine\Repository\DoctrineRepositoryHelper;
use Shredio\Core\Bridge\Doctrine\Repository\DoctrineRepositoryServices;
use Shredio\Core\Bridge\Doctrine\TrackingPolicy\DeferredTrackingPolicy;
use Shredio\Core\Bridge\Symfony\Bundle\Compiler\CoreCompilerPass;
use Shredio\Core\Bridge\Symfony\Bundle\Compiler\PackagerCompilerPass;
use Shredio\Core\Bridge\Symfony\Cache\SymfonyAdapterFactory;
use Shredio\Core\Bridge\Symfony\Cache\SymfonyCacheFactory;
use Shredio\Core\Bridge\Symfony\Environment\SymfonyAppEnvironment;
use Shredio\Core\Bridge\Symfony\Error\ErrorListener;
use Shredio\Core\Bridge\Symfony\Extension\SymfonyExtensionHelper;
use Shredio\Core\Bridge\Symfony\Http\PsrRequestResolver;
use Shredio\Core\Bridge\Symfony\Middleware\PackagingMiddleware;
use Shredio\Core\Bridge\Symfony\Reporter\SymfonyExceptionReporter;
use Shredio\Core\Bridge\Symfony\Rest\RestLoader;
use Shredio\Core\Bridge\Symfony\Rest\SymfonyRestOperationsFactory;
use Shredio\Core\Bridge\Symfony\Security\SymfonyAuthenticator;
use Shredio\Core\Bridge\Symfony\Security\SymfonyUserContext;
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
use Shredio\Core\Database\Rapid\EntityRapidOperationFactory;
use Shredio\Core\Entity\EntityFactory;
use Shredio\Core\Entity\Metadata\ContextExtractor;
use Shredio\Core\Entity\SymfonyEntityFactory;
use Shredio\Core\Environment\AppEnvironment;
use Shredio\Core\Environment\EnvVars;
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
use Shredio\Core\Pagination\PaginationFactory;
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
use Shredio\Core\Security\UserContext;
use Symfony\Bridge\PsrHttpMessage\EventListener\PsrResponseListener;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Serializer\Serializer;
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
		EnvVars::require('APP_ENV', 'The environment the application is running in.');
		EnvVars::require('APP_RUNTIME_ENV', 'The runtime environment the application is running in.');
		EnvVars::require('CACHE_PREFIX', 'Prefix for cache keys used in key-value stores. e.g. myapp:');
		EnvVars::require('CACHE_DSN', 'DSN for the default cache storage. e.g. redis://redis:6379');
		EnvVars::require('AUTH_PASETO_SECRET', 'Secret key for PASETO tokens.');

		$builder->prependExtensionConfig('framework', [
			'cache' => [
				'prefix_seed' => EnvVars::getString('CACHE_PREFIX'),
			],
		]);

		if ($container->env() === 'test') {
			$this->loadTest($container, $builder);
		}

		$this->loadRestRouter($container, $builder);
		$this->loadBasics($container, $builder);
		$this->loadCache($container, $builder, $config['cache'] ?? []);
		$this->loadAppCache($container, $builder);
		$this->loadSecurity($container, $builder);
		$this->loadPsr7($container, $builder);
		$this->loadDoctrine($container, $builder);
		$this->loadPackager($container, $builder);
		$this->loadMiddlewares($container, $builder);
		$this->loadSerializer($container, $builder);
		$this->loadHttpCache($container, $builder);
		$this->loadConsole($container, $builder);
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
				->arrayNode('cache')
					->children()
						->stringNode('namespace')->end()
						->arrayNode('aliases')
							->useAttributeAsKey('name')
							->normalizeKeys(false)
							->arrayPrototype()
								->children()
									->stringNode('prefix')->cannotBeEmpty()->end()
									->stringNode('storage')->end()
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

		// Create CacheFactory
		$resolveAlias = static function (string $name): string {
			return EnvVars::getString('CACHE_PREFIX') . $name;
		};

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
				->args([$cache, $resolveAlias($settings['prefix'])]);

			$collection[$name] = new ReferenceConfigurator($serviceName);
		}

		$services->set(SymfonyCacheFactory::class)
			->args([$collection])
			->arg('$scream', param('kernel.debug'))
			->alias(CacheFactory::class, SymfonyCacheFactory::class);
	}

	public static function createCache(CacheItemPoolInterface $cache, string $prefix): PrefixCache
	{
		return new PrefixCache(new Psr16Cache($cache), $prefix);
	}

	private function loadAppCache(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();
		$services->set('cache.app', AdapterInterface::class)
			->autowire()
			->autoconfigure()
			->factory([SymfonyAdapterFactory::class, 'create'])
			->args([param('env(string:CACHE_DSN)'), param('kernel.cache_dir')])
			->arg('$marshaller', new ReferenceConfigurator('cache.default_marshaller'));
	}

	private function loadSecurity(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();

		$services->set('core.authenticator', SymfonyAuthenticator::class)
			->autowire()
			->autoconfigure();

		$this->addInterfaceService($services, UserContext::class, SymfonyUserContext::class);
		$this->addInterfaceService($services, TokenProvider::class, PasetoProvider::class)
			->arg('$secret', param('env(string:AUTH_PASETO_SECRET)'));

		$provider = $services->set('core.authTokenProvider', AuthTokenProvider::class)
			->autowire();

		if ($container->env() === 'test') {
			$provider->public();
		}

		$provider->alias(AuthTokenProvider::class, 'core.authTokenProvider');
	}

	private function loadPsr7(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
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

		$this->addInterfaceService($services, EntityRapidOperationFactory::class, DoctrineEntityRapidOperationFactory::class);
		$this->addInterfaceService($services, QueryBuilderFactory::class, DefaultQueryBuilderFactory::class);
		$this->addInterfaceService($services, PaginationFactory::class, DoctrinePaginationFactory::class);
		$this->addInterfaceService($services, EntityFactory::class, SymfonyEntityFactory::class);
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

		$services->set(PackagingMiddleware::class)
			->autowire()
			->autoconfigure();
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
	}

	private function loadTest(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();
		$services->set(ErrorHandlerForTests::class)
			->args([service(ErrorHandlerForTests::class . '.inner'), service('testbench')])
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

	private function loadConsole(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();

		$services->set(EncodeTokenCommand::class)
			->autowire()
			->tag('console.command');

		$services->set(DecodeTokenCommand::class)
			->autowire()
			->tag('console.command');

		$services->set(AuthTokenCommand::class)
			->autowire()
			->tag('console.command');
	}

}
