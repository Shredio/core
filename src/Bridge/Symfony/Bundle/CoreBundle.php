<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Bundle;

use LogicException;
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
use Shredio\Core\Bridge\Symfony\Http\PsrRequestResolver;
use Shredio\Core\Bridge\Symfony\Middleware\PackagingMiddleware;
use Shredio\Core\Bridge\Symfony\Reporter\SymfonyExceptionReporter;
use Shredio\Core\Bridge\Symfony\Rest\RestLoader;
use Shredio\Core\Bridge\Symfony\Rest\SymfonyRestOperationsFactory;
use Shredio\Core\Bridge\Symfony\Security\PasetoAuthenticator;
use Shredio\Core\Bridge\Symfony\Security\SymfonyUserProvider;
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
use Shredio\Core\Database\Rapid\EntityRapidOperationFactory;
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
use Shredio\Core\Pagination\PaginationFactory;
use Shredio\Core\Path\Directories;
use Shredio\Core\Reporter\ExceptionReporter;
use Shredio\Core\Rest\Locator\RestControllerLocator;
use Shredio\Core\Rest\Metadata\ControllerMetadataFactory;
use Shredio\Core\Rest\Metadata\DefaultControllerMetadataFactory;
use Shredio\Core\Rest\Metadata\DefaultEndpointMetadataFactory;
use Shredio\Core\Rest\Metadata\EndpointMetadataFactory;
use Shredio\Core\Rest\RestOperationsFactory;
use Shredio\Core\Security\PasetoProvider;
use Shredio\Core\Security\TokenProvider;
use Shredio\Core\Security\UserProvider;
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
use Symfony\Component\DependencyInjection\Loader\Configurator\ServiceConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Serializer\Serializer;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

final class CoreBundle extends AbstractBundle
{

	/**
	 * @param mixed[] $config
	 */
	public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		if (!isset($_ENV['CACHE_PREFIX'])) {
			throw new LogicException('The CACHE_PREFIX environment variable must be set.');
		}

		if (!isset($_ENV['CACHE_DSN'])) {
			throw new LogicException('The CACHE_DSN environment variable must be set.');
		}

		if (!isset($_ENV['PASETO_SECRET'])) {
			throw new LogicException('The PASETO_SECRET environment variable must be set.');
		}

		$builder->prependExtensionConfig('framework', [
			'cache' => [
				'prefix_seed' => $_ENV['CACHE_PREFIX'],
			],
		]);

		$services = $container->services();

		if ($_ENV['APP_ENV'] === 'test') {
			$this->loadTest($container, $builder);
		}

		$services->set(RestLoader::class)
			->autowire()
			->autoconfigure()
			->tag('routing.loader');

		$services->set(RestControllerLocator::class)
			->autowire();

		$this->addService($services, ControllerMetadataFactory::class, DefaultControllerMetadataFactory::class);
		$this->addService($services, EndpointMetadataFactory::class, DefaultEndpointMetadataFactory::class);

		$services->set('directories')
			->autowire()
			->synthetic()
			->alias(Directories::class, 'directories');

		$services->set(SymfonyRestOperationsFactory::class)
			->autowire()
			->alias(RestOperationsFactory::class, SymfonyRestOperationsFactory::class);

		$services->set(SymfonyEntityFactory::class)
			->autowire()
			->alias(EntityFactory::class, SymfonyEntityFactory::class);

		$services->set(ContextExtractor::class);

		$this->loadBasics($container, $builder);
		$this->loadCache($container, $builder, $config['cache'] ?? []);
		$this->loadSecurity($container, $builder);
		$this->loadPsr7($container, $builder);
		$this->loadDoctrine($container, $builder);
		$this->loadPackager($container, $builder);
		$this->loadMiddlewares($container, $builder);
		$this->loadSerializer($container, $builder);
		$this->loadHttpCache($container, $builder);
	}

	private function loadBasics(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();

		$this->addService($services, AppEnvironment::class, SymfonyAppEnvironment::class);
		$this->addService($services, ExceptionReporter::class, SymfonyExceptionReporter::class);
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

		$this->loadAppCache($services);

		// Create CacheFactory
		$resolveAlias = static function (string $name): string {
			if ($_ENV['CACHE_PREFIX']) {
				$name = $_ENV['CACHE_PREFIX'] . $name;
			}

			return $name;
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

	private function loadAppCache(ServicesConfigurator $services): void
	{
		$services->set('cache.app', AdapterInterface::class)
			->factory([SymfonyAdapterFactory::class, 'create'])
			->args([$_ENV['CACHE_DSN'], param('kernel.cache_dir')])
			->arg('$marshaller', new ReferenceConfigurator('cache.default_marshaller'));
	}

	private function loadSecurity(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();
		$services->defaults()
			->autowire()
			->autoconfigure();

		$services->set(PasetoProvider::class)
			->arg('$secret', $_ENV['PASETO_SECRET'])
			->alias(TokenProvider::class, PasetoProvider::class);

		$services->set('core.authenticator', PasetoAuthenticator::class)
			->arg('$idKey', 'val');

		$this->addService($services, UserProvider::class, SymfonyUserProvider::class);
	}

	private function loadPsr7(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();
		$services->defaults()
			->autowire()
			->autoconfigure();

		$services->set(PsrRequestResolver::class)
			->autoconfigure()
			->tag('controller.argument_value_resolver', ['priority' => -100]);
		$services->set(PsrResponseListener::class)
			->autoconfigure();

		$this->addService($services, HttpFoundationFactoryInterface::class, HttpFoundationFactory::class);
		$this->addService($services, HttpMessageFactoryInterface::class, PsrHttpFactory::class);
	}

	private function addService(ServicesConfigurator $services, string $interface, string $class, bool $configure = false): ServiceConfigurator
	{
		$service = $services->set($class);
		$service->autowire()
			->autoconfigure($configure)
			->alias($interface, $class);

		return $service;
	}

	private function loadDoctrine(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();
		$services->defaults()
			->autowire()
			->autoconfigure();

		$services->set(DoctrineEntityRapidOperationFactory::class)
			->alias(EntityRapidOperationFactory::class, DoctrineEntityRapidOperationFactory::class);
		$services->set(DeferredTrackingPolicy::class);
		$services->set(DoctrineRepositoryServices::class);
		$services->set(DoctrineRepositoryHelper::class);

		$this->addService($services, QueryBuilderFactory::class, DefaultQueryBuilderFactory::class);
		$this->addService($services, PaginationFactory::class, DoctrinePaginationFactory::class);

		$services->set(EntityManagerRegistry::class);
	}

	private function loadPackager(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();
		$services->defaults()
			->autowire()
			->autoconfigure();

		$services->set('packager', Packager::class)
			->alias(Packager::class, 'packager')
			->public();

		if (class_exists(Serializer::class)) {
			$this->addService($services, InstructionProcessor::class, SerializationInstructionProcessor::class)
				->tag('packager.processor');
		}

		$this->addService($services, InstructionProcessor::class, FormattingInstructionProcessor::class)
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
		$services->defaults()
			->autowire()
			->autoconfigure();

		$services->set(PackagingMiddleware::class);
	}

	private function loadSerializer(ContainerConfigurator $container, ContainerBuilder $builder): void
	{
		$services = $container->services();
		$services->defaults()
			->autowire()
			->autoconfigure();

		$services->set(SymbolNormalizer::class)
			->tag('serializer.normalizer');

		$services->set(AccountIdNormalizer::class)
			->tag('serializer.normalizer');

		$services->set(KeepObjectNormalizer::class)
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

}
