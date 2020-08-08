<?php declare(strict_types = 1);

namespace App\Config;

class RatesSourceConfig
{
    /** @var string */
    private $ecbSourceUrl;

    /** @var string */
    private $cbrSourceUrl;

    public function __construct(string $ecbSourceUrl, string $cbrSourceUrl)
    {
        $this->ecbSourceUrl = $ecbSourceUrl;
        $this->cbrSourceUrl = $cbrSourceUrl;
    }

    public function getEcbSourceUrl(): string
    {
        return $this->ecbSourceUrl;
    }

    public function getCbrSourceUrl(): string
    {
        return $this->cbrSourceUrl;
    }
}
