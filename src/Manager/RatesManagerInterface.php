<?php declare(strict_types = 1);

namespace App\Manager;

interface RatesManagerInterface
{
    public function updateRates(string $source): void;
    public function clearRatesTable(): void;
}
