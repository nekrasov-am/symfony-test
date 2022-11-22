<?php

namespace App\Commands;

use App\Utils\ExchangeRate;
use App\Utils\FileReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCommand extends Command
{
	private $exchangeRate;
	private $fileReader;

	public function __construct($exchangeRate = null, $fileReader = null)
	{
		$this->exchangeRate = $exchangeRate ?? new ExchangeRate();
		$this->fileReader = $fileReader ?? new FileReader();
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setName('parse');
		$this->addArgument('input_file', InputArgument::REQUIRED, 'Provide the name of input file');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$hasErrors = false;

		$filename = $input->getArgument('input_file');
		try {
			$lines = $this->fileReader->getLines($filename);
		} catch (\Exception $e) {
			$output->writeln($e->getMessage());
			return Command::INVALID;
		}

		foreach ($lines as $line) {
			if ($line) {
				try {
					$json = json_decode($line);
					$country = $this->exchangeRate->getCountryByBin($json->bin);
					if (strtoupper($json->currency) == 'EUR') {
						$rate = 1;
					} else {
						$rate = $this->exchangeRate->getCurrencyExchangeRate(strtoupper($json->currency)) ?? 1;
					}
					$multiplier = $this->exchangeRate->isEu($country) ? 0.01 : 0.02;
					$correctedRate = $json->amount / $rate * $multiplier;
					$correctedRate = ceil($correctedRate * 100) / 100;
					$output->writeln($correctedRate);
				} catch (\Exception $e) {
					$hasErrors = true;
					$output->writeln($e->getMessage());
				}
			}
		}

		return $hasErrors ? Command::FAILURE : Command::SUCCESS;
	}
}