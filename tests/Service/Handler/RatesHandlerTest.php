<?php declare(strict_types = 1);

namespace App\Tests\Service\Fetcher;

use App\Service\Handler\RatesHandler;
use App\Service\Fetcher\RatesFetcher;
use App\Service\Fetcher\RatesFetcherInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RatesHandlerTest extends TestCase
{
    private const ECB_URL = 'test.ecb.local';
    private const CBR_URL = 'test.cbr.local';

    public function testGetEcbRates(): void
    {
        $ratesData = file_get_contents(__DIR__ . '../../../data/ecb_rates.xml');
        $ratesFetcher = $this->mockRatesFetcher();
        $ratesFetcher
            ->method('getEcbRates')
            ->willReturn($ratesData);
        $ratesHandler = new RatesHandler($ratesFetcher);

        $rates = $ratesHandler->getEcbRates();
        $this->assertNotEmpty($rates);
        $this->assertEquals(32, count($rates));
    }

    public function testGetCbrRates(): void
    {
        $ratesData = file_get_contents(__DIR__ . '../../../data/cbr_rates.xml');
        $ratesFetcher = $this->mockRatesFetcher();
        $ratesFetcher
            ->method('getCbrRates')
            ->willReturn($ratesData);

        $ratesHandler = new RatesHandler($ratesFetcher);

        $rates = $ratesHandler->getCbrRates();
        $this->assertNotEmpty($rates);
        $this->assertEquals(34, count($rates));
    }

    /** @return RatesFetcherInterface|MockObject */
    private function mockRatesFetcher(): RatesFetcherInterface
    {
        return $this->createMock(RatesFetcherInterface::class);
    }
}
