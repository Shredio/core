<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use LogicException;
use PHPUnit\Framework\Attributes\After;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\KernelInterface;

trait KernelEnvironment // @phpstan-ignore-line
{

	use KernelTestTrait;

	#[After]
	protected function tearDownKernel(): void
	{
		if (TestHelper::$kernel) {
			TestHelper::$kernel = null;
		}
	}

	protected function getKernel(): KernelInterface
	{
		return TestHelper::$kernel ??= self::bootKernel();
	}

	protected function getContainer(): Container
	{
		try {
			return $this->getKernel()->getContainer()->get('test.service_container');
		} catch (ServiceNotFoundException $e) {
			throw new LogicException('Could not find service "test.service_container". Try updating the "framework.test" config to "true".', 0, $e);
		}
	}

	protected function getTestBench(): TestBench
	{
		return TestHelper::getTestBench($this->getKernel());
	}

}
