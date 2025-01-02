<?php declare(strict_types = 1);

namespace Shredio\Core\Command;

use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use Nette\Neon\Neon;
use Shredio\Core\Security\TokenProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('token:encode', 'Encode payload.')]
final class EncodeTokenCommand extends Command
{

	public function __construct(
		private readonly TokenProvider $tokenProvider,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->addArgument('values', InputArgument::REQUIRED, 'Values to encode.')
			->addOption('ttl', 't', InputOption::VALUE_REQUIRED, 'Time to live in seconds or DateTime\'s constructor format.', 0);
	}

	/**
	 * @throws DateMalformedStringException
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$values = $input->getArgument('values');
		$ttl = $this->getTtl($input);

		if (!is_string($values)) {
			$output->writeln('Values must be a string.');

			return self::FAILURE;
		}

		$decoded = Neon::decode($values);

		$output->writeln($this->tokenProvider->create($decoded, $ttl)->getId());

		return self::SUCCESS;
	}

	/**
	 * @throws DateMalformedStringException
	 */
	private function getTtl(InputInterface $input): ?DateTimeInterface
	{
		$ttl = $input->getOption('ttl');

		if (is_numeric($ttl)) {
			return (new DateTimeImmutable())->modify(sprintf('+%d seconds', $ttl));
		}

		if (is_string($ttl)) {
			return new DateTimeImmutable($ttl);
		}

		return null;
	}

}
