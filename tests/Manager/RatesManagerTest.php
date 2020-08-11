<?php declare(strict_types = 1);

namespace App\Tests\Manager;

use App\Command\UpdateRatesCommand;
use App\Exchange\ConvertorInterface;
use App\Manager\RatesManager;
use App\Request\ExchangeModel;
use App\Repository\RateRepository;
use App\Service\Handler\RatesHandlerInterface;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use PHPUnit\Framework\TestCase;

class RatesManagerTest extends TestCase
{
    public function testUpdateRates(): void
    {
        $ratesHandler = $this->mockRatesHandler();
        $ratesHandler->method('getEcbRates')->willReturn([
            'USD' => '1.1783',
            'JPY' => '124.97',
            'BGN' => '1.9558',
            'CZK' => '26.155',
        ]);
        $em = $this->mockEntityManager();
        $em->method('persist');
        $em->method('flush');

        $manager = new RatesManager($ratesHandler, $em, $this->mockConvertor());

        $manager->updateRates(UpdateRatesCommand::ECB_SOURCE);
        $this->assertTrue(true);
    }

    public function testClearRatesTable(): void
    {
        $ratesHandler = $this->mockRatesHandler();
        $platform = $this->createMock(AbstractPlatform::class);
        $platform->method('getTruncateTableSQL')->with('rates');
        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn($platform);
        $connection->method('executeUpdate');
        $em = $this->mockEntityManager();
        $em->method('getConnection')->willReturn($connection);

        $manager = new RatesManager($ratesHandler, $em, $this->mockConvertor());
        $manager->clearRatesTable();
        $this->assertTrue(true);
    }

    public function testGetAvailableRates(): void
    {
        $repository = $this->mockRatesRepository();
        $ratesHandler = $this->mockRatesHandler();
        $em = $this->mockEntityManager();
        $em->method('getRepository')->willReturn($repository);

        $manager = new RatesManager($ratesHandler, $em, $this->mockConvertor());
        $rates = $manager->getAvailableRates();
        $this->assertEquals(1, count($rates));
        $this->assertEquals(RatesManager::ECB_SOURCE_CURRENCY, key($rates));
        $this->assertEquals(3, count($rates[RatesManager::ECB_SOURCE_CURRENCY]));
    }

    public function testConvertMoney(): void
    {
        $model = new ExchangeModel(RatesManager::ECB_SOURCE_CURRENCY, 'USD', 150);
        $repository = $this->mockRatesRepository();
        $ratesHandler = $this->mockRatesHandler();
        $em = $this->mockEntityManager();
        $em->method('getRepository')->willReturn($repository);
        $convertor = $this->mockConvertor();
        $convertor
            ->method('convert')
            ->with($model, [
                RatesManager::ECB_SOURCE_CURRENCY => [
                    'USD' => '1.1783',
                    'JPY' => '124.97',
                    'BGN' => '1.9558',
                ],
            ])
            ->willReturn([
                'baseCurrency' => RatesManager::ECB_SOURCE_CURRENCY,
                'baseAmount' => 150,
                'targetCurrency' => 'USD',
                'targetAmount' => 176.745,
            ]);

        $manager = new RatesManager($ratesHandler, $em, $convertor);
        $result = $manager->convertMoney($model);

        $this->assertEquals(176.745, $result['targetAmount']);
    }

    /** @return RatesHandlerInterface|MockObject */
    private function mockRatesHandler(): RatesHandlerInterface
    {
        return $this->createMock(RatesHandlerInterface::class);
    }

    /** @return EntityManagerInterface|MockObject */
    private function mockEntityManager(): EntityManagerInterface
    {
        return $this->createMock(EntityManagerInterface::class);
        ;
    }

    /** @return ConvertorInterface|MockObject */
    private function mockConvertor(): ConvertorInterface
    {
        return $this->createMock(ConvertorInterface::class);
        ;
    }

    /** @return RateRepository|MockObject */
    private function mockRatesRepository(): RateRepository
    {
        $repository = $this->createMock(RateRepository::class);
        $repository->method('getRates')->willReturn([
            [
                'baseCurrency' => RatesManager::ECB_SOURCE_CURRENCY,
                'targetCurrency' => 'USD',
                'exchangeRate' => '1.1783',
            ],
            [
                'baseCurrency' => RatesManager::ECB_SOURCE_CURRENCY,
                'targetCurrency' => 'JPY',
                'exchangeRate' => '124.97',
            ],
            [
                'baseCurrency' => RatesManager::ECB_SOURCE_CURRENCY,
                'targetCurrency' => 'BGN',
                'exchangeRate' => '1.9558',
            ],
        ]);

        return $repository;
    }
}
