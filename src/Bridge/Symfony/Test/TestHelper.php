<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use LogicException;
use Shredio\Core\Struct\LazyValue;
use Shredio\Core\Test\TestData;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\KernelInterface;

final class TestHelper
{

	public static ?KernelInterface $kernel = null;

	/** @var LazyValue<TestData|null> */
	private readonly LazyValue $testData;

	public readonly TestHelperInternals $internals;

	/**
	 * @param callable(): ?TestData $getTestData
	 */
	public function __construct(callable $getTestData)
	{
		$this->testData = new LazyValue($getTestData);
		$this->internals = new TestHelperInternals($this, $this->testData);
	}

	public function getTestData(): ?TestData
	{
		return $this->testData->getValue();
	}

	public function reset(): void
	{
		$this->testData->reset();
		$this->internals->reset();
	}

	public static function getTestBench(KernelInterface $kernel): TestBench
	{
		/** @var TestBench */
		return self::getContainer($kernel)->get('testbench');
	}

	public static function getContainer(KernelInterface $kernel): Container
	{
		try {
			/** @var Container */
			return $kernel->getContainer()->get('test.service_container');
		} catch (ServiceNotFoundException $e) {
			throw new LogicException('Could not find service "test.service_container". Try updating the "framework.test" config to "true".', 0, $e);
		}
	}

	public static function getClient(KernelInterface $kernel): KernelBrowser
	{
		try {
			/** @var KernelBrowser $client */
			$client = $kernel->getContainer()->get('test.client');
		} catch (ServiceNotFoundException) {
			if (class_exists(KernelBrowser::class)) {
				throw new \LogicException('You cannot create the client used in functional tests if the "framework.test" config is not set to true.');
			}
			throw new \LogicException('You cannot create the client used in functional tests if the BrowserKit component is not available. Try running "composer require symfony/browser-kit".');
		}

		return $client;
	}

}
