<?php

namespace App\Repository;

use App\Entity\Group;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class GroupRepository extends EntityRepository {

    /**
     * @param User $user
     * @return int
     */
    public function countUserIsAdminOf(User $user) {
        $qb = $this->_em
            ->createQueryBuilder()
            ->select('count(1)')
            ->from(Group::class, 'g')
            ->leftJoin('g.admins', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $user->getId());

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param User $user
     * @return QueryBuilder
     */
    public function findAllUserIsAdminOfQueryBuilder(User $user) {
        $qb = $this->_em
            ->createQueryBuilder()
            ->select('g')
            ->from(Group::class, 'g')
            ->leftJoin('g.admins', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $user->getId());

        return $qb;
    }

    public function findAllUserIsAdminOf(User $user) {
        return $this->findAllUserIsAdminOfQueryBuilder($user)
            ->getQuery()->getResult();
    }
}