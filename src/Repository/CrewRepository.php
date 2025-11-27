<?php

namespace App\Repository;

use App\Entity\Crew;
use App\Entity\Ship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Crew>
 */
class CrewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Crew::class);
    }

    public function findOneByCaptainOnShip(Ship $ship, ?Crew $exclude = null): ?Crew
    {
        $qb = $this->createQueryBuilder('c')
            ->join('c.shipRoles', 'r')
            ->andWhere('c.ship = :ship')
            ->andWhere('r.code = :cap')
            ->setParameter('ship', $ship)
            ->setParameter('cap', 'CAP')
            ->setMaxResults(1);

        if ($exclude?->getId()) {
            $qb->andWhere('c.id != :exclude')
                ->setParameter('exclude', $exclude->getId());
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param bool $needCaptain
     * @return array
     */
    public function getCrewNotInAnyShip(bool $needCaptain): array
    {
        $crew = $this->createQueryBuilder('c')
            ->join('c.shipRoles', 'r')
            ->where('c.ship IS NULL')
            ->getQuery()->getResult();

        $result = [];
        /** @var Crew $c */
        foreach($crew as $c) {
            if (!$needCaptain) {
                if ($c->isCaptain()) {
                    continue;
                }
            }
            $result[] = $c;
        }

        return $result;
    }
}
