<?php

namespace App\Repository;

use DateTimeInterface;
use App\Entity\Reservation;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PhpParser\Node\Scalar\MagicConst\Dir;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findByDay(DateTimeInterface $day): array
    {
        $start = (clone $day)->setTime(0, 0, 0);
        $end = (clone $day)->setTime(23, 59, 59);

        return $this->createQueryBuilder('r')
            ->andWhere('r.startAt < :end')
            ->andWhere('r.endAt >= :start')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    public function findByUserByDay($user, DateTimeInterface $day): array
    {
        $start = (clone $day)->setTime(0, 0, 0);
        $end = (clone $day)->setTime(23, 59, 59);

        return $this->createQueryBuilder('r')
            ->andWhere('r.startAt < :end')
            ->andWhere('r.endAt >= :start')
            ->andWhere('r.utilisateur = :utilisateur')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('utilisateur', $user)
            ->getQuery()
            ->getResult();
    }

    public function findByServiceByDay($idService, DateTimeInterface $date): array
    {
        $start = (clone $date)->setTime(0,0,0);
        $end = (clone $date)->setTime(23.59,59);
        return $this->createQueryBuilder('r')
            ->andWhere('r.startAt < :end')
            ->andWhere('r.endAt >= :start')
            ->andWhere('r.service = :idservice')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter(':idservice', $idService)
            ->getQuery()
            ->getResult();
    }

    public function isAvailable($idService, DateTimeInterface $start, DateTimeInterface $end)
    {
        $result = $this->createQueryBuilder('r')
            ->andWhere('r.startAt < :end')
            ->andWhere('r.endAt >= :start')
            ->andWhere('r.service = :service')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('service', $idService)
            ->getQuery()
            ->getResult();

        return count($result) ? false : true;
    }

    //    /**
    //     * @return Reservation[] Returns an array of Reservation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reservation
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
