<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }


    private const DAYS_BEFORE_REJECTED_REMOVAL = 7;
    public const PAGINATOR_PER_PAGE = 2;




    public function countOldRejected(): int
        {
            return $this->getOldRejectedQueryBuilder()->select('COUNT(c.id)')->getQuery()->getSingleScalarResult();
        }
    
        public function deleteOldRejected(): int
        {
            return $this->getOldRejectedQueryBuilder()->delete()->getQuery()->execute();
        }
    
        private function getOldRejectedQueryBuilder(): QueryBuilder
        {
            return $this->createQueryBuilder('c')
                ->andWhere('c.state = :state_rejected or c.state = :state_spam')
                ->andWhere('c.createdAt < :date')
                ->setParameters([
                    'state_rejected' => 'rejected',
                    'state_spam' => 'spam',
                    'date' => new \DateTime(-self::DAYS_BEFORE_REJECTED_REMOVAL.' days'),
                ])
            ;
        }

    public function getCommentPaginator(Product $product, int $offset): Paginator
   {
       $query = $this->createQueryBuilder('c')
          ->andWhere('c.product = :product')
          ->andWhere('c.state = :state')
           ->setParameter('product', $product)
           ->setParameter('state', 'published')
           ->orderBy('c.createdAt', 'DESC')
          ->setMaxResults(self::PAGINATOR_PER_PAGE)
           ->setFirstResult($offset)
           ->getQuery()
       ;

      return new Paginator($query);
    }

    // /**
    //  * @return Review[] Returns an array of Review objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Review
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
