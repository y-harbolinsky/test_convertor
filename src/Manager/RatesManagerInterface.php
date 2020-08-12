<?php declare(strict_types = 1);

namespace App\Manager;

use App\Request\ExchangeModel;

interface RatesManagerInterface
{
    public function updateRates(string $source): void;
    public function clearRatesTable(): void;
    public function getAvailableRates(): array;
    public function convertMoney(ExchangeModel $model): array;
}
