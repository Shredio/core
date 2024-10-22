<?php declare(strict_types = 1);

namespace Shredio\Core\Entity\Metadata;

use ReflectionClass;
use Spiral\Core\Attribute\Singleton;

#[Singleton]
final class ContextExtractor
{

	/**
	 * @param class-string $className
	 * @param class-string<Context> $type
	 * @return mixed[]
	 */
	public function extract(string $className, string $type): array
	{
		$reflection = new ReflectionClass($className);
		$context = [];

		foreach ($reflection->getAttributes($type) as $reflectionAttribute) {
			/** @var Context $attribute */
			$attribute = $reflectionAttribute->newInstance();

			if ($attribute->groups !== null) {
				$context['groups'] = $attribute->groups;
			}
		}

		return $context;
	}

}
