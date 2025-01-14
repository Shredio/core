<?php declare(strict_types = 1);

namespace Shredio\Core\Xml;

use LogicException;
use XMLReader;

final readonly class XmlTemplate
{

	/** @var array<string, (callable(string $inner): string)|string|null> */
	private array $transformers;

	/**
	 * @param array<string, (callable(string $inner): string)|string|null> $transformers
	 */
	private function __construct(
		private XMLReader $reader,
		array $transformers,
	)
	{
		$transformers['root'] ??= null;

		$this->transformers = $transformers;
	}

	public function output(): string
	{
		$this->reader->read(); // skip the root element

		return $this->transform();
	}

	private function transform(): string
	{
		if (!array_key_exists($this->reader->localName, $this->transformers)) {
			trigger_error(sprintf('No transformer for element %s', $this->reader->localName), E_USER_WARNING);

			return '';
		}

		$transformer = $this->transformers[$this->reader->localName];

		if ($transformer === null) {
			return $this->outputElement();
		} else if (is_string($transformer)) {
			if ($transformer[0] === '@') {
				$this->skipElement();

				return $this->specialTransform($transformer);
			}

			return sprintf('<%s>', $transformer) . $this->outputElement() . $this->getClosingTag($transformer);
		} else {
			return $transformer($this->outputElement());
		}
	}

	private function specialTransform(string $transformer): string
	{
		$pos = strpos($transformer, ' ');

		if ($pos === false) {
			throw new LogicException(sprintf('Invalid transformer %s', $transformer));
		}

		$id = substr($transformer, 1, $pos - 1);

		if ($id === 'html') {
			return mb_substr($transformer, $pos + 1);
		} else if ($id === 'text') {
			return htmlspecialchars(mb_substr($transformer, $pos + 1));
		} else {
			throw new LogicException(sprintf('Unknown special transformer %s', $id));
		}
	}

	private function getClosingTag(string $transformer): string
	{
		$pos = strpos($transformer, ' ');

		if ($pos === false) {
			return sprintf('</%s>', $transformer);
		}

		return sprintf('</%s>', substr($transformer, 0, $pos));
	}

	private function skipElement(): void
	{
		$depth = 0;

		while ($this->reader->read()) {
			if ($this->reader->nodeType === XMLReader::ELEMENT) {
				$depth++;
			} else if ($this->reader->nodeType === XMLReader::END_ELEMENT) {
				if ($depth === 0) {
					break;
				}

				$depth--;
			}
		}
	}

	private function outputElement(): string
	{
		$output = '';

		while ($this->reader->read()) {
			if ($this->reader->localName === '#text') {
				$output .= $this->reader->value;
			} else if ($this->reader->nodeType === XMLReader::ELEMENT) {
				$output .= $this->transform();
			} else if ($this->reader->nodeType === XMLReader::END_ELEMENT) {
				break;
			}
		}

		return $output;
	}

	/**
	 * @param array<string, (callable(string $inner): string)|string|null> $transformers
	 * @example TemplateTranslator::parse($str2, ['highlight' => 'em class="important"']); // <em class="important">...</em>
	 * @example TemplateTranslator::parse($str4, ['highlight' => fn (string $content) => "<mark>$content</mark>"]); // <mark>...</mark>
	 * @example TemplateTranslator::parse($str, ['highlight' => 'span']); // <span>...</span>
	 * @example TemplateTranslator::parse($str, ['highlight' => '@html <mark>content</mark>']); // <mark>content</mark>
	 * @example TemplateTranslator::parse($str, ['highlight' => '@text text']); // text
	 * @example TemplateTranslator::parse($str, ['highlight' => null]); // Keep the original content
	 */
	public static function parse(string $str, array $transformers): string
	{
		if (!str_starts_with($str, '<root>')) {
			trigger_error('The input string must start with <root>', E_USER_WARNING);

			return $str;
		}

		$reader = XMLReader::XML($str);

		if (is_bool($reader)) {
			trigger_error('Failed to parse the input string', E_USER_WARNING);

			return strip_tags($str);
		}

		return (new self($reader, $transformers))->output();
	}

}
