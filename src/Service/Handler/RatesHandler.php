<?php declare(strict_types = 1);

namespace App\Service\Handler;

use App\Service\Fetcher\RatesFetcherInterface;

class RatesHandler implements RatesHandlerInterface
{
    /** RatesFetcherInterface */
    private $ratesFetcher;

    public function __construct(RatesFetcherInterface $ratesFetcher)
    {
        $this->ratesFetcher = $ratesFetcher;
    }

    public function getEcbRates(): array
    {
        return $this->parseEcbRates(
            $this->ratesFetcher->getEcbRates()
        );
    }

    public function getCbrRates(): array
    {
        return $this->parseCbrRates(
            $this->ratesFetcher->getCbrRates()
        );
    }

    private function parseEcbRates(string $content): array
    {
        $rates = [];
        $data = simplexml_load_string($content);

        foreach($data->Cube->Cube->Cube as $currency) {
            $rates[(string)$currency['currency']] = (string)$currency['rate'];
        }

        return $rates;
    }

    private function parseCbrRates(string $content): array
    {
        $rates = [];
        $data = simplexml_load_string($content);

        foreach($data->Valute as $currency) {
            $rates[(string)$currency->CharCode] = (string)$currency->Value;
        }

        return $rates;
    }
}
