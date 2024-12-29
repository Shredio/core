<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use PHPUnit\Framework\Attributes\After;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\BrowserKitAssertionsTrait;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * @internal use WebEnvironment instead
 */
trait WebTestTrait // @phpstan-ignore-line
{

	use KernelEnvironment;
	use BrowserKitAssertionsTrait;

	#[After]
	protected function tearDownWeb(): void
	{
		self::getClient(null);
	}

	/**
	 * Creates a KernelBrowser.
	 *
	 * @param array $options An array of options to pass to the createKernel method
	 * @param array $server  An array of server parameters
	 */
	protected static function createClient(array $options = [], array $server = []): KernelBrowser
	{
		if (static::$booted) {
			throw new \LogicException(\sprintf('Booting the kernel before calling "%s()" is not supported, the kernel should only be booted once.', __METHOD__));
		}

		$kernel = static::bootKernel($options);

		try {
			$client = $kernel->getContainer()->get('test.client');
		} catch (ServiceNotFoundException) {
			if (class_exists(KernelBrowser::class)) {
				throw new \LogicException('You cannot create the client used in functional tests if the "framework.test" config is not set to true.');
			}
			throw new \LogicException('You cannot create the client used in functional tests if the BrowserKit component is not available. Try running "composer require symfony/browser-kit".');
		}

		$client->setServerParameters($server);

		/** @var KernelBrowser */
		return self::getClient($client);
	}

}
