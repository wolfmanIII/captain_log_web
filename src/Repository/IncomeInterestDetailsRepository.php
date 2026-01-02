<?php

namespace App\Repository;

use App\Entity\IncomeInterestDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IncomeInterestDetails>
 */
class IncomeInterestDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IncomeInterestDetails::class);
    }
}
