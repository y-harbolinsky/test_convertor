<?php declare(strict_types = 1);

namespace App\Tests\Service\Fetcher;

use App\Config\RatesSourceConfig;
use App\Exception\FetchException;
use App\Service\Fetcher\RatesFetcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class RatesFetcherTest extends TestCase
{
    private const ECB_URL = 'test.ecb.local';
    private const CBR_URL = 'test.cbr.local';

    /** @dataProvider getEcbDataProvider */
    public function testGetEcbRates(int $httpMethod): void
    {
        $ratesData = file_get_contents(__DIR__ . '../../../data/cbr_rates.xml');
        $response = $this->mockResponse();
        $response->method('getStatusCode')->willReturn($httpMethod);
        $response
            ->method('getContent')
            ->willReturn($ratesData);

        $client = $this->mockHttpClient();
        $client
            ->method('request')
            ->with(Request::METHOD_GET, self::ECB_URL, [])
            ->willReturn($response);
        $config = $this->mockConfig();
        $config->method('getEcbSourceUrl')->willReturn(self::ECB_URL);

        $ratesFetcher = new RatesFetcher($client, $config);

        if (Response::HTTP_OK !== $httpMethod) {
            $this->expectException(FetchException::class);
        }

        $rates = $ratesFetcher->getEcbRates();
        $this->assertNotEmpty($rates);
    }

    /** @dataProvider getCbrDataProvider */
    public function testGetCbrRates(int $httpMethod): void
    {
        $ratesData = file_get_contents(__DIR__ . '../../../data/cbr_rates.xml');
        $response = $this->mockResponse();
        $response->method('getStatusCode')->willReturn($httpMethod);
        $response
            ->method('getContent')
            ->willReturn($ratesData);

        $client = $this->mockHttpClient();
        $client
            ->method('request')
            ->with(Request::METHOD_GET, self::CBR_URL, [])
            ->willReturn($response);
        $config = $this->mockConfig();
        $config->method('getCbrSourceUrl')->willReturn(self::CBR_URL);

        $ratesFetcher = new RatesFetcher($client, $config);

        if (Response::HTTP_OK !== $httpMethod) {
            $this->expectException(FetchException::class);
        }

        $rates = $ratesFetcher->getCbrRates();
        $this->assertNotEmpty($rates);
    }

    public function getEcbDataProvider(): array
    {
        return [
            [Response::HTTP_OK],
            [Response::HTTP_BAD_REQUEST],
        ];
    }

    public function getCbrDataProvider(): array
    {
        return [
            [Response::HTTP_OK],
            [Response::HTTP_NOT_FOUND],
        ];
    }

    /** @return HttpClientInterface|MockObject */
    private function mockHttpClient(): HttpClientInterface
    {
        return $this->createMock(HttpClientInterface::class);
    }

    /** @return RatesSourceConfig|MockObject */
    private function mockConfig(): RatesSourceConfig
    {
        return $this->createMock(RatesSourceConfig::class);;
    }

    /** @return ResponseInterface|MockObject */
    private function mockResponse(): ResponseInterface
    {
        return $this->createMock(ResponseInterface::class);;
    }
}
