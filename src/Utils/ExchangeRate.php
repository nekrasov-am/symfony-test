<?php

namespace App\Utils;

use Symfony\Component\HttpClient\HttpClient;

class ExchangeRate
{
	private $httpClient;
	private $currencyExchangeRates;

	public function __construct()
	{
		$this->httpClient = HttpClient::create();
		$this->currencyExchangeRates = [];
	}

	public function getCountryByBin(string $bin): string
	{
		$url = 'https://lookup.binlist.net/'.urlencode($bin);
		$response = $this->httpClient->request('GET', $url);
		if ($response->getStatusCode() == 200) {
			$data = $response->toArray();
			return $data['country']['alpha2'];
		} else {
			throw new \Exception($response->getContent());
		}
	}

	public function getCurrencyExchangeRate(string $currency): float
	{
		if (array_key_exists($currency, $this->currencyExchangeRates)) {
			return $this->currencyExchangeRates[$currency];
		}

		$url = 'https://api.apilayer.com/exchangerates_data/latest';
		$response = $this->httpClient->request('GET', $url, [
			'headers' => ['apikey' => $_ENV['EXCHANGE_API_KEY']]
		]);
		if ($response->getStatusCode() == 200) {
			$data = $response->toArray();
			$this->currencyExchangeRates = $data['rates'];
			return $data['rates'][$currency];
		} else {
			throw new \Exception($response->getContent());
		}
	}

	public function isEu(string $code): bool
	{
		$euCodes = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT',
			'LT', 'LU', 'LV', 'MT', 'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'];
		return in_array(strtoupper($code), $euCodes);
	}
}