<?php declare(strict_types = 1);

namespace Shredio\Core\Package;

use InvalidArgumentException;
use Nette\Utils\Json;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Shredio\Core\Package\Instruction\PackingInstruction;
use Shredio\Core\Package\Processor\InstructionProcessor;

final class Packager
{

	/** @var InstructionProcessor[] */
	private array $processors = [];

	/** @var array<class-string, InstructionProcessor> */
	private array $lookupTable = [];

	public function addProcessor(InstructionProcessor $processor): void
	{
		$this->processors[] = $processor;
	}

	/**
	 * @param array<PackingInstruction|null> $instructions
	 */
	public function packSource(
		mixed $source,
		array $instructions,
		ResponseInterface $response,
		ServerRequestInterface $request,
	): ResponseInterface
	{
		$item = $this->pack(new ItemToPack($source, $response, $request), $instructions);

		return $item->response
			->withHeader('Content-Type', 'application/json')
			->withBody(Stream::create(Json::encode($item->value)));
	}

	/**
	 * @param iterable<mixed> $source
	 * @param array<PackingInstruction|null> $instructions
	 */
	public function packSources(
		iterable $source,
		array $instructions,
		ResponseInterface $response,
		ServerRequestInterface $request,
	): ResponseInterface
	{
		$sources = [];

		foreach ($source as $item) {
			$item = $this->pack(new ItemToPack($item, $response, $request), $instructions);

			$sources[] = $item->value;
			$response = $item->response;
		}

		return $response
			->withHeader('Content-Type', 'application/json')
			->withBody(Stream::create(Json::encode($sources)));
	}

	/**
	 * @param array<PackingInstruction|null> $instructions
	 */
	private function pack(ItemToPack $item, array $instructions): ItemToPack
	{
		foreach ($instructions as $instruction) {
			if (!$instruction) {
				continue;
			}

			$item = $this->processItem($item, $instruction);
		}

		return $item;
	}

	private function processItem(ItemToPack $item, PackingInstruction $instruction): ItemToPack
	{
		if (isset($this->lookupTable[$instruction::class])) {
			$result = $this->lookupTable[$instruction::class]->processItem($item, $instruction);

			if ($result) {
				return $result;
			}
		}

		foreach ($this->processors as $processor) {
			$result = $processor->processItem($item, $instruction);

			if ($result) {
				$this->lookupTable[$instruction::class] = $processor;

				return $result;
			}
		}

		throw new InvalidArgumentException(sprintf('No processor found for instruction %s.', $instruction::class));
	}

}
