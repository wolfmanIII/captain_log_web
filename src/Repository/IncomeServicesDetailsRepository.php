<?php

namespace App\Repository;

use App\Entity\IncomeServicesDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IncomeServicesDetails>
 */
class IncomeServicesDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IncomeServicesDetails::class);
    }
}
