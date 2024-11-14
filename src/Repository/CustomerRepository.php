<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 */
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function findTopMedications(int $limit = 30): array {
        return $this->createQueryBuilder('c')
            ->select('c.order_name, COUNT(c.id) as count')
            ->groupBy('c.order_name')
            ->orderBy('count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findTopCountriesByGroup(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "
            WITH country_counts AS (
                SELECT 
                    group_name, 
                    country, 
                    COUNT(*) AS customer_count
                FROM 
                    customer
                GROUP BY 
                    group_name, country
            ),
            max_counts AS (
                SELECT 
                    group_name, 
                    MAX(customer_count) AS max_customer_count
                FROM 
                    country_counts
                GROUP BY 
                    group_name
            )
            SELECT 
                c.group_name,
                GROUP_CONCAT(c.country, ', ') as countries,
                c.customer_count AS max_customer_count
            FROM 
                country_counts c
            JOIN 
                max_counts m 
            ON 
                c.group_name = m.group_name 
                AND c.customer_count = m.max_customer_count
            GROUP BY 
                c.group_name, 
                c.customer_count
            ORDER BY CAST(c.group_name as Integer);
        ";

        return $conn->executeQuery($sql)->fetchAllAssociative();
    }

    public function findTopSourcesByStatus(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "
            WITH source_count AS (
                SELECT 
                    status, 
                    source, 
                    COUNT(*) AS customer_count 
                FROM 
                    customer 
                GROUP BY 
                    status, source
            ),
            max_count AS (
                SELECT 
                    status, 
                    MAX(customer_count) AS max_customer_count 
                FROM 
                    source_count 
                GROUP BY 
                    status
            )
            SELECT 
                sc.status,
                GROUP_CONCAT(sc.source, ', ') AS sources
            FROM 
                source_count sc
            JOIN 
                max_count mc 
            ON 
                sc.status = mc.status
                AND sc.customer_count = mc.max_customer_count
            GROUP BY 
                sc.status
            ORDER BY 
                sc.status;
        ";

        return $conn->executeQuery($sql)->fetchAllAssociative();
    }

    // If I was using PostgreSQL, I would use REGEXP_REPLACE, LENGTH and SUM directly in the query instead of manually iterating over the results
    public function totalConsonantsInCustomerNames(): int
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "SELECT customer FROM customer";
        $customers = $conn->executeQuery($sql)->fetchFirstColumn();
        
        $consonantCount = 0;
        foreach ($customers as $customer) {
            $onlyLetters = preg_replace('/[^a-zA-Z]/', '', $customer);
            $onlyConsonants = preg_replace('/[aeiouAEIOU]/', '', $onlyLetters);
            $consonantCount += strlen($onlyConsonants);
        }
        
        return $consonantCount;
    }
}
