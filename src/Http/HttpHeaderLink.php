<?php declare(strict_types = 1);

namespace Shredio\Core\Http;

use Shredio\Core\Struct\Set;
use Psr\Http\Message\ResponseInterface;

final class HttpHeaderLink
{

	private string $value = '';

	/** @var Set<string> */
	private Set $links;

	public function __construct()
	{
		$this->links = Set::createString();
	}

	public function add(string $link, string $rel, string $as): self
	{
		if ($this->links->has($link)) {
			return $this;
		}

		$this->value = sprintf('%s<%s>; rel="%s"; as="%s", ', $this->value, $link, $rel, $as);
		$this->links->add($link);

		return $this;
	}

	public function getValue(): string
	{
		return substr($this->value, 0, -2);
	}

	public function with(ResponseInterface $response): ResponseInterface
	{
		if ($value = $this->getValue()) {
			return $response->withHeader('Link', $value);
		}

		return $response;
	}

}
