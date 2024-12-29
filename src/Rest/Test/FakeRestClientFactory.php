<?php declare(strict_types = 1);

namespace Shredio\Core\Rest\Test;

use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Shredio\Core\Common\Reflection\ReflectionHelper;
use Shredio\Core\Rest\Metadata\ControllerMetadataFactory;
use Shredio\Core\Rest\Metadata\DefaultControllerMetadataFactory;
use Shredio\Core\Rest\Metadata\DefaultEndpointMetadataFactory;
use Shredio\Core\Rest\Metadata\EndpointMetadataFactory;
use Shredio\Core\Rest\Test\Attribute\TestControllerMethod;

final class FakeRestClientFactory
{

	/** @var callable(FakeRequest $request): FakeResponse */
	private $request;

	/**
	 * @param callable(FakeRequest $request): FakeResponse $request
	 */
	public function __construct(
		private readonly TestCase $case,
		callable $request,
		private readonly ControllerMetadataFactory $controllerMetadataFactory = new DefaultControllerMetadataFactory(new DefaultEndpointMetadataFactory()),
	)
	{
		$this->request = $request;
	}

	/**
	 * @param array{class-string, non-empty-string}|null $controllerAction
	 */
	public function create(?array $controllerAction = null): FakeRestClient
	{
		if ($controllerAction === null) {
			$controllerAction = $this->getControllerFromTestCase()?->action;
		}

		if ($controllerAction === null) {
			throw new LogicException('Controller action must be provided');
		}

		$controller = $controllerAction[0];
		$method = $controllerAction[1];

		$controllerMetadata = $this->controllerMetadataFactory->create(new ReflectionClass($controller));

		if (!$controllerMetadata) {
			throw new LogicException(sprintf('Controller metadata not found for %s', $controller));
		}

		return new FakeRestClient($controllerMetadata, $method, $this->request);
	}

	private function getControllerFromTestCase(): ?TestControllerMethod
	{
		$reflection = new ReflectionMethod($this->case, $this->case->name());

		return ReflectionHelper::getAttribute($reflection, TestControllerMethod::class);
	}

}
