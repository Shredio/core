<?php declare(strict_types = 1);

namespace Shredio\Core\Http;

use Shredio\Core\Attribute\Preload;
use Shredio\Core\Common\Reflection\ReflectionHelper;
use Cycle\ORM\EntityProxyInterface;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

final class ProjectEntityPreloader implements EntityPreloader
{

	private const array Preloaders = [
		'account' => '/api/accounts/?',
	];

	public function __construct(
		private PropertyAccessorInterface $propertyAccessor,
	)
	{
	}

	/**
	 * @param object[] $entities
	 */
	public function preload(
		ServerRequestInterface $request,
		ResponseInterface $response,
		array $entities,
	): ResponseInterface
	{
		$first = array_key_first($entities);

		if ($first === null) {
			return $response;
		}

		$preloads = $this->getPreloads($request);

		if (!$preloads) {
			return $response;
		}

		$preloaders = $this->createPreloaders($this->createReflection($entities[$first]), $preloads);

		$header = new HttpHeaderLink();

		foreach ($entities as $entity) {
			foreach ($preloaders as $preloader) {
				$link = $preloader($entity);

				if ($link) {
					$header->add($link, 'preload', 'fetch');
				}
			}
 		}

		return $header->with($response);
	}

	/**
	 * @param ReflectionClass<object> $reflectionClass
	 * @param string[] $preloads
	 * @return array<callable(object $entity): ?string>
	 */
	private function createPreloaders(ReflectionClass $reflectionClass, array $preloads): array
	{
		$preloaders = [];

		foreach ($preloads as $preload) {
			$reflection = $reflectionClass->getProperty($preload);

			$preload = ReflectionHelper::getAttribute($reflection, Preload::class);

			if ($preload && isset(self::Preloaders[$preload->role])) {
				$link = self::Preloaders[$preload->role];

				$preloaders[] = function (object $entity) use ($link, $reflection): ?string {
					$value = $this->propertyAccessor->getValue($entity, $reflection->name);

					if ($value === null) {
						return null;
					}

					if (!is_scalar($value)) {
						throw new LogicException('Preload value must be scalar');
					}

					return str_replace('?', (string) $value, $link);
				};
			}
		}

		return $preloaders;
	}

	/**
	 * @template T of object
	 * @param T $object
	 * @return ReflectionClass<T>
	 */
	private function createReflection(object $object): ReflectionClass
	{
		if ($object instanceof EntityProxyInterface) {
			$pos = strpos($object::class, " ");
			if ($pos === false) {
				return new ReflectionClass($object);
			}

			return new ReflectionClass(substr($object::class, 0, $pos)); // @phpstan-ignore-line
		}

		return new ReflectionClass($object);
	}

	/**
	 * @return string[]
	 */
	private function getPreloads(ServerRequestInterface $request): array
	{
		$preload = $request->getHeaderLine('Preload');

		if (!$preload) {
			return [];
		}

		return array_map('trim', explode(',', $preload));
	}

}
