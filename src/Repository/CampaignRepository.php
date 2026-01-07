<?php

namespace App\Repository;

use App\Entity\Campaign;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Campaign>
 */
class CampaignRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campaign::class);
    }

    /**
     * @return Campaign[]
     */
    public function findAllForUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.ships', 's')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
            ->groupBy('c.id')
            ->orderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array{title?: string, starting_year?: int|null} $filters
     *
     * @return array{items: Campaign[], total: int}
     */
    public function findWithFilters(array $filters, int $page, int $limit): array
    {
        $qb = $this->createQueryBuilder('c');

        if (!empty($filters['title'])) {
            $title = '%'.strtolower($filters['title']).'%';
            $qb->andWhere('LOWER(c.title) LIKE :title')
                ->setParameter('title', $title);
        }

        if (!empty($filters['starting_year'])) {
            $qb->andWhere('c.startingYear = :startingYear')
                ->setParameter('startingYear', (int) $filters['starting_year']);
        }

        $qb->orderBy('c.startingYear', 'DESC')
            ->addOrderBy('c.title', 'ASC');

        $query = $qb->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($query);

        return [
            'items' => iterator_to_array($paginator),
            'total' => $paginator->count(),
        ];
    }
}
