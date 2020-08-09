<?php declare(strict_types = 1);

namespace App\Manager;

use App\Command\UpdateRatesCommand;
use App\Entity\Rate;
use App\Service\Handler\RatesHandlerInterface;
use Doctrine\ORM\EntityManagerInterface;

class RatesManager implements RatesManagerInterface
{
    private const ECB_SOURCE_CURRENCY = 'EUR';
    private const CBR_SOURCE_CURRENCY = 'RUB';

    /** RatesHandlerInterface */
    private $ratesHandler;

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(RatesHandlerInterface $ratesHandler, EntityManagerInterface $em)
    {
        $this->ratesHandler = $ratesHandler;
        $this->em = $em;
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
}
