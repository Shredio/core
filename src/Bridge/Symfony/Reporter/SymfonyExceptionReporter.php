<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Reporter;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use ReflectionAttribute;
use ReflectionClass;
use Shredio\Core\Environment\AppEnvironment;
use Shredio\Core\Reporter\ExceptionReporter;
use Symfony\Component\HttpKernel\Attribute\WithLogLevel;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final readonly class SymfonyExceptionReporter implements ExceptionReporter
{

	public function __construct(
		private AppEnvironment $appEnvironment,
		private ?LoggerInterface $logger = null,
	)
	{
	}

	public function report(Throwable $exception): void
	{
		if ($this->appEnvironment->isProduction()) {
			$this->logger?->log($this->resolveLogLevel($exception), $exception->getMessage(), ['exception' => $exception]);

			return;
		}

		throw $exception;
	}

	/**
	 * Resolves the level to be used when logging the exception.
	 */
	private function resolveLogLevel(\Throwable $throwable): string
	{
		/** @var WithLogLevel|null $withLogLevel */
		$withLogLevel = $this->getInheritedAttribute($throwable::class, WithLogLevel::class);

		if ($withLogLevel) {
			return $withLogLevel->level;
		}

		if (!$throwable instanceof HttpExceptionInterface || $throwable->getStatusCode() >= 500) {
			return LogLevel::CRITICAL;
		}

		return LogLevel::ERROR;
	}

	/**
	 * @template T of object
	 * @param class-string $class
	 * @param class-string<T> $attribute
	 * @return T|null
	 */
	private function getInheritedAttribute(string $class, string $attribute): ?object
	{
		$class = new ReflectionClass($class);
		$interfaces = [];
		$attributeReflector = null;
		$parentInterfaces = [];

		do {
			if ($attributes = $class->getAttributes($attribute, ReflectionAttribute::IS_INSTANCEOF)) {
				$attributeReflector = $attributes[0];
				$implements = class_implements($class->name);

				if ($implements) {
					$parentInterfaces = $implements;
				}
				break;
			}

			$interfaces[] = class_implements($class->name);
		} while ($class = $class->getParentClass());

		while ($interfaces) {
			$ownInterfaces = array_diff_key(array_pop($interfaces), $parentInterfaces);
			$parentInterfaces += $ownInterfaces;

			foreach ($ownInterfaces as $interface) {
				$class = new ReflectionClass($interface);

				if ($attributes = $class->getAttributes($attribute, ReflectionAttribute::IS_INSTANCEOF)) {
					$attributeReflector = $attributes[0];
				}
			}
		}

		/** @var T|null */
		return $attributeReflector?->newInstance();
	}

}
