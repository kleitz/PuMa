<?php
/*
 * This file is part of the project tutteli/puma published under the Apache License 2.0
 * For the full copyright and license information, please have a look at LICENSE in the
 * root folder or visit https://github.com/robstoll/PuMa
 */

namespace Tutteli\AppBundle\Entity;

class PurchaseRepository extends ARepository
{
    protected function getEntityIdentifier() {
        return 'TutteliAppBundle:Purchase';
    }
    
    public function getLastUpdatedForMonthOfYear($month, $year) {
        $result = $this->createQueryBuilderPurchasesForMonthOfYear($month, $year)
            ->orderBy('p.updatedAt', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();
        if (count($result) > 0) {
            return $result[0];
        }
        return null;
    }
    
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function createQueryBuilderPurchasesForMonthOfYear($month, $year) {
        $from = new \DateTime($year.'-'.$month.'-01T00:00:00');
        $to = new \DateTime($year.'-'.$month.'-01T00:00:00');
        $to->add(new \DateInterval('P1M'));
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        return $queryBuilder->select('p')
                ->from($this->getEntityIdentifier(), 'p')
                ->where($queryBuilder->expr()->andX(
                        $queryBuilder->expr()->gte('p.pumaDate', ':from'),
                        $queryBuilder->expr()->lt('p.pumaDate', ':to')
                ))          
                ->setParameter('from', $from)
                ->setParameter('to', $to);
    }
    
    public function getForMonthOfYear($month, $year) {
        return $this->createQueryBuilderPurchasesForMonthOfYear($month, $year)
            ->orderBy('p.pumaDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
