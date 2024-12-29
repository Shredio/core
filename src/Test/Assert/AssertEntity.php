<?php declare(strict_types = 1);

namespace Shredio\Core\Test\Assert;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;

final readonly class AssertEntity
{

	/**
	 * @param class-string $entity
	 */
	public function __construct(
		public string $entity,
		public EntityManagerInterface $em,
	)
	{
	}

	public function assertCount(int $expected): void
	{
		$actual = $this->em->getRepository($this->entity)->count();

		Assert::assertSame(
			$expected,
			$actual,
			sprintf('Expected %s entities in the table, actual are %s.', $expected, $actual),
		);
	}

}
