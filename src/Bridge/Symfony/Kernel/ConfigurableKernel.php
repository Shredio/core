<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Kernel;

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

		foreach ($this->services as $id => $service) {
			$container->set($id, $service);
		}
	}

}
