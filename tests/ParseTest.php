<?php

use App\Commands\ParseCommand;
use App\Utils\ExchangeRate;
use App\Utils\FileReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ParseTest extends TestCase
{
	public function testParse(): void
	{
		$application = new Application();

		$fileMock = $this->getMockBuilder('FileReader')->setMethods(['getLines'])->getMock();
		$fileMock->expects($this->once())->method('getLines')->willReturn(['{"bin":"45717360","amount":"100.00","currency":"USD"}']);

		$rateMock = $this->getMockBuilder('ExchangeRate')->setMethods(['getCountryByBin', 'getCurrencyExchangeRate', 'isEu'])->getMock();
		$rateMock->expects($this->once())->method('getCountryByBin')->willReturn('GB');
		$rateMock->expects($this->once())->method('getCurrencyExchangeRate')->willReturn(1);
		$rateMock->expects($this->once())->method('isEu')->willReturn(true);

		$application->add(new ParseCommand($rateMock, $fileMock));
		$command = $application->find('parse');
		$commandTester = new CommandTester($command);

		$commandTester->execute(['input_file' => '']);
		$this->assertEquals('1', trim($commandTester->getDisplay()));
	}
}