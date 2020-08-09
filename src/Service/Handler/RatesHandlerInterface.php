<?php declare(strict_types = 1);

namespace App\Service\Handler;

interface RatesHandlerInterface
{
    public function getEcbRates(): array;
    public function getCbrRates(): array;
}
