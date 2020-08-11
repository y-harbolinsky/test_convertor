<?php declare(strict_types = 1);

namespace App\Tests\Exchange;

use App\Manager\RatesManager;
use App\Exception\ApiBadRequestException;
use App\Exchange\Convertor;
use App\Request\ExchangeModel;
use PHPUnit\Framework\TestCase;

class ConvertorTest extends TestCase
{
    public function testConvertWithNoRates(): void
    {
        $model = new ExchangeModel(RatesManager::ECB_SOURCE_CURRENCY, 'USD', 10);
        $convertor = new Convertor();

        $this->expectException(ApiBadRequestException::class);
        $convertor->convert($model, []);
    }

    public function testConvertWithNotExistingRates(): void
    {
        $model = new ExchangeModel(RatesManager::CBR_SOURCE_CURRENCY, 'UAH', 10);
        $convertor = new Convertor();

        $this->expectException(ApiBadRequestException::class);
        $convertor->convert($model, [
            RatesManager::ECB_SOURCE_CURRENCY => [
                'USD' => '1.1783',
                'JPY' => '124.97',
                'BGN' => '1.9558',
            ],
        ]);
    }

    public function testConvert(): void
    {
        $convertor = new Convertor();

        $model = new ExchangeModel(RatesManager::ECB_SOURCE_CURRENCY, RatesManager::ECB_SOURCE_CURRENCY, 55.57);
        $result = $convertor->convert($model, [
            RatesManager::ECB_SOURCE_CURRENCY => [
                'USD' => '1.1783',
                'JPY' => '124.97',
                'BGN' => '1.9558',
            ],
        ]);
        $this->assertEquals(55.57, $result['targetAmount']);

        $model = new ExchangeModel(RatesManager::ECB_SOURCE_CURRENCY, 'JPY', 30);
        $result = $convertor->convert($model, [
            RatesManager::ECB_SOURCE_CURRENCY => [
                'USD' => '1.1783',
                'JPY' => '124.97',
                'BGN' => '1.9558',
            ],
        ]);
        $this->assertEquals('3,749.1000', $result['targetAmount']);

        $model = new ExchangeModel('JPY', 'USD', 177);
        $result = $convertor->convert($model, [
            RatesManager::ECB_SOURCE_CURRENCY => [
                'USD' => '1.1783',
                'JPY' => '124.97',
                'BGN' => '1.9558',
            ],
        ]);
        $this->assertEquals('1.6689', $result['targetAmount']);

        $model = new ExchangeModel('BGN', 'JPY', 500);
        $result = $convertor->convert($model, [
            RatesManager::ECB_SOURCE_CURRENCY => [
                'USD' => '1.1783',
                'JPY' => '124.97',
                'BGN' => '1.9558',
            ],
        ]);
        $this->assertEquals('31,948.5632', $result['targetAmount']);

        $model = new ExchangeModel('JPY', 'BGN', 333);
        $result = $convertor->convert($model, [
            RatesManager::ECB_SOURCE_CURRENCY => [
                'USD' => '1.1783',
                'JPY' => '124.97',
                'BGN' => '1.9558',
            ],
        ]);
        $this->assertEquals('5.2115', $result['targetAmount']);
    }
}
