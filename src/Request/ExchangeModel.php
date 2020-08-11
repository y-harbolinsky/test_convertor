<?php declare(strict_types = 1);

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;

/** @codeCoverageIgnore */
class ExchangeModel
{
    /**
     * @var string
     * @Assert\NotNull
     * @Assert\Currency
     */
    private $baseCurrency;

    /**
     * @Assert\NotNull
     * @Assert\Currency
     * @var string
     */
    private $targetCurrency;

    /**
     * @Assert\NotNull
     * @Assert\GreaterThan(0)
     * @var float
     */
    private $amount;

    public function __construct(string $baseCurrency, string $targetCurrency, float $amount)
    {
        $this->baseCurrency = $baseCurrency;
        $this->targetCurrency = $targetCurrency;
        $this->amount = $amount;
    }

    public function getBaseCurrency(): string
    {
        return $this->baseCurrency;
    }

    public function getTargetCurrency(): string
    {
        return $this->targetCurrency;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }
}
