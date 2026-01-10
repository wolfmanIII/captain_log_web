<?php

namespace App\Repository;

use App\Entity\Ship;
use App\Entity\ShipAmendment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ShipAmendment>
 */
class ShipAmendmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShipAmendment::class);
    }

    /**
     * @return ShipAmendment[]
     */
    public function findForShip(User $user, Ship $ship): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->andWhere('a.ship = :ship')
            ->setParameter('user', $user)
            ->setParameter('ship', $ship)
            ->orderBy('a.effectiveYear', 'DESC')
            ->addOrderBy('a.effectiveDay', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
