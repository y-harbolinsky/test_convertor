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
        $response = $this->mockResponse();
        $response->method('getStatusCode')->willReturn($httpMethod);
        $response
            ->method('getContent')
            ->willReturn('
                <gesmes:Envelope xmlns:gesmes="http://www.gesmes.org/xml/2002-08-01" xmlns="http://www.ecb.int/vocabulary/2002-08-01/eurofxref">
                <gesmes:subject>Reference rates</gesmes:subject>
                <gesmes:Sender>
                <gesmes:name>European Central Bank</gesmes:name>
                </gesmes:Sender>
                <Cube>
                    <Cube time="2020-08-11">
                        <Cube currency="USD" rate="1.1783"/>
                        <Cube currency="JPY" rate="124.97"/>
                        <Cube currency="BGN" rate="1.9558"/>
                    </Cube>
                </Cube>
                </gesmes:Envelope>
            ');

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
        $response = $this->mockResponse();
        $response->method('getStatusCode')->willReturn($httpMethod);
        $response
            ->method('getContent')
            ->willReturn('
                <ValCurs Date="12.08.2020" name="Foreign Currency Market">
                    <Valute ID="R01010">
                        <NumCode>036</NumCode>
                        <CharCode>AUD</CharCode>
                        <Nominal>1</Nominal>
                        <Name>Австралийский доллар</Name>
                    <Value>52,4648</Value>
                    </Valute>
                    <Valute ID="R01820">
                        <NumCode>392</NumCode>
                        <CharCode>JPY</CharCode>
                        <Nominal>100</Nominal>
                        <Name>Японских иен</Name>
                        <Value>68,9432</Value>
                    </Valute>
                </ValCurs>
            ');

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
