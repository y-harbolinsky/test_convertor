<?php declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Rate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @codeCoverageIgnore */
class RateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rate::class);
    }

    public function getRates(): array
    {
        return $this->createQueryBuilder('r')
            ->select('r.baseCurrency, r.targetCurrency, r.exchangeRate')
            ->getQuery()
            ->getArrayResult();
    }
}
