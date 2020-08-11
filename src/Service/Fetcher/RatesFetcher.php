<?php declare(strict_types = 1);

namespace App\Service\Fetcher;

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

    public function getEcbRates(): string
    {
        $response = $this->client->request(
            Request::METHOD_GET,
            $this->config->getEcbSourceUrl()
        );

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new FetchException('unable to fetch rates. Please try again later.');
        }

        return $response->getContent();
    }

    public function getCbrRates(): string
    {
        $response = $this->client->request(
            Request::METHOD_GET,
            $this->config->getCbrSourceUrl()
        );

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new FetchException('unable to fetch rates. Please try again later.');
        }

        return $response->getContent();
    }
}
