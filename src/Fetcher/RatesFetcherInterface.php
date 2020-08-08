<?php declare(strict_types = 1);

namespace App\Fetcher;

interface RatesFetcherInterface
{
    public function getEcbRates(): array;
    public function getCbrRates(): array;
}
