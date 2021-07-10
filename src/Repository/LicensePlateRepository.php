<?php

namespace App\Repository;

use App\Entity\LicensePlate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method LicensePlate|null find($id, $lockMode = null, $lockVersion = null)
 * @method LicensePlate|null findOneBy(array $criteria, array $orderBy = null)
 * @method LicensePlate[]    findAll()
 * @method LicensePlate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LicensePlateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, LicensePlate::class);
        $this->security = $security;
    }

//    /**
//     * @return QueryBuilder Returns an array of LicensePlate objects
//     */
//    public function findUserLp(int $userId): QueryBuilder
//    {
//        $q = $this->createQueryBuilder('l');
//
//        if($userId)
//        {
//            $q ->innerJoin('l.user', 'u')
//                ->andWhere('u.id = :user')
//                ->setParameter('user', $userId);
//        }
//
//        $q->orderBy('l.licensePlate', 'ASC');
//
//        return $q;
//    }


//    /**
//     * @return QueryBuilder Returns an array of LicensePlate objects
//     */
//    public function findOneBySomeField(): ?LicensePlate
//    {
//        return $this->createQueryBuilder('lp')
//            ->andWhere('lp.user = :val')
//            ->setParameter('val', $this->security->getUser());
//        ;
//    }

}
