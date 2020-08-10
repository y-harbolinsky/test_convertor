<?php declare(strict_types = 1);

namespace App\Manager;

use App\Command\UpdateRatesCommand;
use App\Entity\Rate;
use App\Exception\ApiBadRequestException;
use App\Exchange\ConvertorInterface;
use App\Request\ExchangeModel;
use App\Repository\RateRepository;
use App\Service\Handler\RatesHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

class RatesManager implements RatesManagerInterface
{
    public const ECB_SOURCE_CURRENCY = 'EUR';
    public const CBR_SOURCE_CURRENCY = 'RUB';

    /** RatesHandlerInterface */
    private $ratesHandler;

    /** @var EntityManagerInterface */
    private $em;

    /** @var ConvertorInterface */
    private $convertor;

    public function __construct(
        RatesHandlerInterface $ratesHandler,
        EntityManagerInterface $em,
        ConvertorInterface $convertor
    ) {
        $this->ratesHandler = $ratesHandler;
        $this->em = $em;
        $this->convertor = $convertor;
    }

    public function updateRates(string $source): void
    {
        $this->em->beginTransaction();

        try {
            switch (true) {
                case (UpdateRatesCommand::ECB_SOURCE === $source):
                    $this->updateEcbRates();
                    break;
                case (UpdateRatesCommand::CBR_SOURCE === $source):
                    $this->updateCbrRates();
                    break;
                default:
                    $this->updateAllRates();
                    break;
            }

            $this->em->flush();
            $this->em->commit();
        } catch (\Throwable $exception) {
            // Log error
            $this->em->rollback();
        }
    }

    public function clearRatesTable(): void
    {
        $connection = $this->em->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeUpdate($platform->getTruncateTableSQL('rates'));
    }

    public function getAvailableRates(): array
    {
        $rates = $this->getRatesRepository()->getRates();

        if (empty($rates)) {
            throw new ApiBadRequestException('No rates available.');
        }

        return $this->prepareRatesArray($rates);
    }

    public function convertMoney(ExchangeModel $model): array
    {
        return $this->convertor->convert(
            $model,
            $this->getAvailableRates()
        );
    }

    private function updateEcbRates(): void
    {
        $rates = $this->ratesHandler->getEcbRates();

        foreach ($rates as $targetCurrency => $exchangeRate) {
            $this->createNewRate(self::ECB_SOURCE_CURRENCY, $targetCurrency, (float)$exchangeRate);
        }
    }

    private function updateCbrRates(): void
    {
        $rates = $this->ratesHandler->getCbrRates();

        foreach ($rates as $targetCurrency => $exchangeRate) {
            $this->createNewRate(self::CBR_SOURCE_CURRENCY, $targetCurrency, (float)$exchangeRate);
        }
    }

    private function createNewRate(string $baseCurrency, string $targetCurrency, float $exchangeRate): void
    {
        $rate = (new Rate())
            ->setBaseCurrency($baseCurrency)
            ->setTargetCurrency($targetCurrency)
            ->setExchangeRate($exchangeRate)
        ;
        $this->em->persist($rate);
    }

    private function updateAllRates(): void
    {
        $this->updateEcbRates();
        $this->updateCbrRates();
    }

    private function prepareRatesArray(array $ratesData): array
    {
        $rates = [];
        foreach ($ratesData as $rateData) {
            $rates[$rateData['baseCurrency']][$rateData['targetCurrency']] = $rateData['exchangeRate'];
        }

        return $rates;
    }

    private function getRatesRepository(): RateRepository
    {
        return $this->em->getRepository(Rate::class);
    }
}
