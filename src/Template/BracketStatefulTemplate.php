<?php declare(strict_types = 1);

namespace Shredio\Core\Template;

final class BracketStatefulTemplate implements TemplateEngine
{

	/**
	 * @param mixed[] $parameters
	 */
	public function __construct(
		private readonly array $parameters,
	)
	{
	}

	/**
	 * @param mixed[] $parameters
	 */
	public function render(string $template, array $parameters = []): string
	{
		return BracketTemplate::render($template, $parameters + $this->parameters);
	}

}
