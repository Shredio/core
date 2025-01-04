<?php declare(strict_types = 1);

namespace Shredio\Core\Bridge\Symfony\Test;

use LogicException;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use Shredio\Core\Rest\Test\FakeRequest;
use Shredio\Core\Rest\Test\FakeResponse;
use Shredio\Core\Rest\Test\FakeRestClient;
use Shredio\Core\Rest\Test\FakeRestClientFactory;
use Shredio\Core\Security\InMemoryUser;
use Shredio\Core\Test\Assert\HttpExpectation;
use Shredio\Core\Test\Authentication\Actor;
use Shredio\Core\Test\Authentication\ForNone;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

trait HttpRestEnvironment // @phpstan-ignore-line
{

	use WebEnvironment;

	#[Before]
	protected function setUpHttpRest(): void
	{
		$instance = TestHelper::getInstance($this);
		$instance->initialize();
	}

	#[After]
	protected function tearDownHttpRest(): void
	{
		foreach ($this->providedData() as $data) {
			if ($data instanceof HttpExpectation && !$data->used) {
				throw new LogicException('HttpExpectation was not used.');
			}
		}
		TestHelper::getInstance($this)->reset();
	}

	/**
	 * @param array{class-string, non-empty-string}|null $controllerAction
	 */
	protected function fakeRestHttp(?array $controllerAction = null): FakeRestClient
	{
		$client = TestHelper::getClient($this->getKernel());
		self::getClient($client); // rewrite the client

		$client->disableReboot();

		/** @var UrlGeneratorInterface $urlGenerator */
		$urlGenerator = $client->getKernel()->getContainer()->get('router');
		$psr17Factory = new Psr17Factory();
		$psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);

		$factory = new FakeRestClientFactory($this, function (FakeRequest $request) use ($client, $urlGenerator, $psrHttpFactory): FakeResponse {
			$testBench = TestHelper::getTestBench($client->getKernel());

			if (($actor = $request->actor) && !$actor instanceof ForNone) {
				TestHelper::getInstance($this)->tryFillActor($actor);

				$testBench->loginUser(new InMemoryUser($actor->getId()->toOriginal(), $actor->getRoles(), $actor->getLanguage()));
			}

			$url = $urlGenerator->generate($request->controllerMetadata->getRouteName($request->endpointMetadata), $request->parameters);
			$server = [];

			foreach ($request->headers as $name => $values) {
				$server['HTTP_' . strtoupper(str_replace('-', '_', $name))] = implode(', ', $values);
			}

			$cookieJar = $client->getCookieJar();
			$cookieJar->clear();

			foreach ($request->cookies as $cookie) {
				$cookieJar->set(new Cookie($cookie->name, $cookie->value));
			}

			if ($request->query) {
				if (str_contains($url, '?')) {
					$url .= '&' . http_build_query($request->query);
				} else {
					$url .= '?' . http_build_query($request->query);
				}
			}

			$client->request(
				$request->method,
				$url,
				server: $server,
				content: $request->body?->getContents(),
				changeHistory: false,
			);

			$testBench->reset();

			return new FakeResponse($psrHttpFactory->createResponse($client->getResponse()));
		});

		$httpClient = $factory->create($controllerAction);

		if ($actor = TestHelper::getInstance($this)->getActorToSignIn()) {
			$httpClient->withActor($actor);
		}

		return $httpClient;
	}

	public function createActor(Actor $actor): Actor
	{
		return TestHelper::getInstance($this)->createActor($actor);
	}

}
