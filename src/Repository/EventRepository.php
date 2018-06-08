<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Group;
use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository {
    public function findForGroups(array $groups, \DateTime $threshold = null) {
        $groupIds = array_map(function(Group $group) { return $group->getId(); }, $groups);

        $qbInner = $this->_em->createQueryBuilder()
            ->select('eInner.id')
            ->from(Event::class, 'eInner')
            ->leftJoin('eInner.groups', 'gInner')
            ->where('gInner.id IN (:groups)');

        $qb = $this->_em->createQueryBuilder()
            ->select(['e', 'u', 'g', 'f', 's', 'c'])
            ->from(Event::class, 'e')
            ->leftJoin('e.comments', 'c')
            ->leftJoin('e.groups', 'g')
            ->leftJoin('e.files', 'f')
            ->leftJoin('e.participants', 's')
            ->leftJoin('e.createdBy', 'u')
            ->orderBy('e.start', 'asc')
            ->where(
                $this->_em->getExpressionBuilder()->in('e.id', $qbInner->getDQL())
            )
            ->setParameter('groups', $groupIds);

        if($threshold !== null) {
            $qb->andWhere('e.start >= :start')
                ->setParameter('start', $threshold);
        }

        return $qb->getQuery()->getResult();
    }

    public function countForGroups(array $groups, \DateTime $threshold = null) {
        $groupIds = array_map(function(Group $group) { return $group->getId(); }, $groups);

        $qbInner = $this->_em->createQueryBuilder()
            ->select('eInner.id')
            ->from(Event::class, 'eInner')
            ->leftJoin('eInner.groups', 'gInner')
            ->where('gInner.id IN (:groups)');

        $qb = $this->_em->createQueryBuilder()
            ->select('COUNT(DISTINCT e.id)')
            ->from(Event::class, 'e')
            ->leftJoin('e.comments', 'c')
            ->leftJoin('e.groups', 'g')
            ->leftJoin('e.files', 'f')
            ->leftJoin('e.participants', 's')
            ->leftJoin('e.createdBy', 'u')
            ->orderBy('e.start', 'asc')
            ->where(
                $this->_em->getExpressionBuilder()->in('e.id', $qbInner->getDQL())
            )
            ->setParameter('groups', $groupIds);

        if($threshold !== null) {
            $qb->andWhere('e.start >= :start')
                ->setParameter('start', $threshold);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}