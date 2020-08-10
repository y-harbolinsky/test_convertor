<?php declare(strict_types = 1);

namespace App\Entity;

use App\Repository\RateRepository;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @codeCoverageIgnore
 * @ORM\Entity(repositoryClass=RateRepository::class)
 * @ORM\Table(name="rates", indexes={@ORM\Index(name="base_currency_idx", columns={"base_currency"})})
 */
class Rate
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="base_currency", type="string", length=3)
     */
    private $baseCurrency;

    /**
     * @var string
     * @ORM\Column(name="target_currency", type="string", length=3)
     */
    private $targetCurrency;

    /**
     * @var float
     * @ORM\Column(name="exchange_rate", type="decimal", precision=13, scale=8)
     */
    private $exchangeRate;

    /**
     * @var \DateTimeInterface
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    public function getId(): int
    {
        return $this->id;
    }

    public function getBaseCurrency(): ?string
    {
        return $this->baseCurrency;
    }

    public function setBaseCurrency(string $baseCurrency): self
    {
        $this->baseCurrency = $baseCurrency;

        return $this;
    }

    public function getTargetCurrency(): ?string
    {
        return $this->targetCurrency;
    }

    public function setTargetCurrency(string $targetCurrency): self
    {
        $this->targetCurrency = $targetCurrency;

        return $this;
    }

    public function getExchangeRate(): ?float
    {
        return $this->exchangeRate;
    }

    public function setExchangeRate(float $exchangeRate): self
    {
        $this->exchangeRate = $exchangeRate;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }
}
