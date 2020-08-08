<?php declare(strict_types = 1);

namespace App\Fetcher;

use App\Config\RatesSourceConfig;
use App\Exception\FetchException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RatesFetcher implements RatesFetcherInterface
{
    /** HttpClientInterface */
    private $client;

    /** @var RatesSourceConfig */
    private $config;

    public function __construct(HttpClientInterface $client, RatesSourceConfig $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    public function getEcbRates(): array
    {
        $response = $this->client->request(
            Request::METHOD_GET,
            $this->config->getEcbSourceUrl()
        );

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \FetchException('unable to fetch rates. Please try again later.');
        }

        return $this->parseEcbRates($response->getContent());
    }

    public function getCbrRates(): array
    {
        $response = $this->client->request(
            Request::METHOD_GET,
            $this->config->getCbrSourceUrl()
        );

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \FetchException('unable to fetch rates. Please try again later.');
        }

        return $this->parseCbrRates($response->getContent());
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
