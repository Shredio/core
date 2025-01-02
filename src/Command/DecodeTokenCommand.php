<?php declare(strict_types = 1);

namespace Shredio\Core\Command;

use Nette\Neon\Neon;
use Shredio\Core\Security\TokenProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('token:decode', 'Decode token to payload.')]
final class DecodeTokenCommand extends Command
{

	public function __construct(
		private readonly TokenProvider $tokenProvider,
	)
	{
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->addArgument('value', InputArgument::REQUIRED, 'Value to decode.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$value = $input->getArgument('value');

		if (!is_string($value)) {
			$output->writeln('Value must be a string.');

			return self::FAILURE;
		}

		$token = $this->tokenProvider->load($value);

		if (!$token) {
			$output->writeln('Invalid token.');

			return self::FAILURE;
		}

		$output->writeln(Neon::encode($token->getPayload()));

		return self::SUCCESS;
	}

}
