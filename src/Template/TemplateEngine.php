<?php declare(strict_types = 1);

namespace Shredio\Core\Template;

interface TemplateEngine
{

	/**
	 * @param mixed[] $parameters
	 */
	public function render(string $template, array $parameters = []): string;

}
