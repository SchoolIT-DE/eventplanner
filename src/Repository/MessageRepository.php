<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class MessageRepository extends EntityRepository {

    public function findAllForUser(User $user) {
        $qb = $this->_em
            ->createQueryBuilder()
            ->select('m')
            ->from(Message::class, 'm')
            ->leftJoin('m.group', 'g')
            ->leftJoin('g.members', 'gm')
            ->where('gm.id = :id')
            ->setParameter('id', $user->getId());

        return $qb->getQuery()->getResult();
    }

    public function countMessagesForUser(User $user) {
        $qb = $this->_em
            ->createQueryBuilder()
            ->select('COUNT(m.id)')
            ->from(Message::class, 'm')
            ->leftJoin('m.group', 'g')
            ->leftJoin('g.members', 'gm')
            ->where('gm.id = :id')
            ->setParameter('id', $user->getId());

        return $qb->getQuery()->getSingleScalarResult();
    }
}