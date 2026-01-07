<?php

namespace App\Repository;

use App\Entity\AnnualBudget;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AnnualBudget>
 */
class AnnualBudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnnualBudget::class);
    }

    /**
     * @return AnnualBudget[]
     */
    public function findAllForUser(User $user): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.user = :user')
            ->setParameter('user', $user)
            ->orderBy('b.startYear', 'DESC')
            ->addOrderBy('b.startDay', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOneForUser(int $id, User $user): ?AnnualBudget
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.id = :id')
            ->andWhere('b.user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param array{ship?: int, start?: string, end?: string, campaign?: int} $filters
     *
     * @return array{items: AnnualBudget[], total: int}
     */
    public function findForUserWithFilters(User $user, array $filters, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.ship', 's')
            ->leftJoin('s.campaign', 'c')
            ->addSelect('s', 'c')
            ->andWhere('b.user = :user')
            ->setParameter('user', $user);

        if ($filters['ship'] !== null) {
            $qb->andWhere('s.id = :ship')
                ->setParameter('ship', (int) $filters['ship']);
        }

        $startKey = $this->parseDayYearFilter($filters['start'] ?? '', false);
        if ($startKey !== null) {
            $qb->andWhere('(b.startYear * 1000 + b.startDay) >= :startKey')
                ->setParameter('startKey', $startKey);
        }

        $endKey = $this->parseDayYearFilter($filters['end'] ?? '', true);
        if ($endKey !== null) {
            $qb->andWhere('(b.endYear * 1000 + b.endDay) <= :endKey')
                ->setParameter('endKey', $endKey);
        }

        if ($filters['campaign'] !== null) {
            $qb->andWhere('c.id = :campaign')
                ->setParameter('campaign', (int) $filters['campaign']);
        }

        $qb->orderBy('b.startYear', 'DESC')
            ->addOrderBy('b.startDay', 'DESC');

        $query = $qb->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($query);

        return [
            'items' => iterator_to_array($paginator),
            'total' => $paginator->count(),
        ];
    }

    private function parseDayYearFilter(string $value, bool $isEnd): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (str_contains($value, '/')) {
            [$day, $year] = array_map('trim', explode('/', $value, 2));
            if (!ctype_digit($day) || !ctype_digit($year)) {
                return null;
            }

            return ((int) $year) * 1000 + (int) $day;
        }

        if (!ctype_digit($value)) {
            return null;
        }

        $year = (int) $value;
        $day = $isEnd ? 999 : 1;

        return $year * 1000 + $day;
    }
}
