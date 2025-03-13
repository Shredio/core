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
	protected function createClient(array $options = [], array $server = []): KernelBrowser
	{
		$client = TestHelper::getClient($this->getKernel());
		$client->setServerParameters($server);

		/** @var KernelBrowser */
		return self::getClient($client);
	}

}
