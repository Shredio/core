<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Kernel;

use Shredio\Core\Path\Directories;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

trait ConfigurableKernel // @phpstan-ignore-line
{

	/** @var array<string, object> */
	private array $services = [];

	public function setService(string $id, object $service): self
	{
		$this->services[$id] = $service;

		return $this;
	}

	protected function initializeContainer(): void
	{
		parent::initializeContainer();

		$container = $this->getContainer();

		$this->startup();

		$this->services['directories'] ??= new Directories([
			Directories::Root => $this->getProjectDir(),
			Directories::Cache => $this->getCacheDir(),
			Directories::Log => $this->getLogDir(),
			...$this->getCustomDirectories(),
		]);

		foreach ($this->services as $id => $service) {
			$container->set($id, $service);
		}
	}

	protected function startup(): void
	{
		// This method can be overridden in the child class to perform any startup tasks.
	}

	/**
	 * @return array<string, string>
	 */
	protected function getCustomDirectories(): array
	{
		return [];
	}

	public function handle(
		Request $request,
		int $type = HttpKernelInterface::MAIN_REQUEST,
		bool $catch = true,
	): Response
	{
		if ($request->getPathInfo() === '/healthz') {
			return new Response('OK');
		}

		return parent::handle($request, $type, $catch);
	}

}
