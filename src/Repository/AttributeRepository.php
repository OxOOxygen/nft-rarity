<?php

namespace App\Repository;

use App\Entity\Asset;
use App\Entity\Attribute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method Attribute|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attribute|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attribute[]    findAll()
 * @method Attribute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttributeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attribute::class);
    }

    // /**
    //  * @return Attribute[] Returns an array of Attribute objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Attribute
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function createOrGet(Attribute $attribute): Attribute
    {
        $existingAttribute = $this->findOneBy(['type' => $attribute->getType(), 'value' => $attribute->getValue(), 'project' => $attribute->getProject()]);

        if ($existingAttribute !== null) {
            return $existingAttribute;
        }

        $this->getEntityManager()->persist($attribute);
        $this->getEntityManager()->flush();

        return $attribute;
    }

    /**
     * @return string[]
     */
    public function getDistinctTypes(int $projectId): array
    {
        $queryBuilder = $this->createQueryBuilder('attribute');

        return $queryBuilder
            ->select('distinct(attribute.type)')
            ->where($queryBuilder->expr()->eq('attribute.project', $projectId))
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
    }

    public function getRarities(int $projectId): array
    {
        // SELECT attribute.type, count(asset_attribute.asset_id) FROM attribute INNER JOIN asset_attribute ON (asset_attribute.attribute_id = attribute.id) WHERE true GROUP BY attribute.type;

        $queryBuilder = $this->createQueryBuilder('attribute');
        $rarities = $queryBuilder
            ->select('attribute.type')
            ->addSelect('attribute.value')
            ->addSelect('count(assets) as total')
            ->innerJoin('attribute.assets', 'assets')
            ->where($queryBuilder->expr()->eq('attribute.project', $projectId))
            ->groupBy('attribute.value')
            ->getQuery()
            ->getResult();

        $raritiesMappedByValue = [];

        $assetCount = $this->getEntityManager()->getRepository(Asset::class)->countByProject(1);

        foreach ($rarities as $rarity) {
            $raritiesMappedByValue[$rarity['value']] = [
                'type' => $rarity['type'],
                'abs' => $rarity['total'],
                'percentage' => $rarity['total'] / $assetCount,
            ];
        }

        return $raritiesMappedByValue;
    }

    public function getDistinctValuesForType(int $projectId, string $type): array
    {
        $queryBuilder = $this->createQueryBuilder('attribute');

        return $queryBuilder
            ->select('distinct(attribute.value)')
            ->where($queryBuilder->expr()->eq('attribute.project', $projectId))
            ->andWhere($queryBuilder->expr()->eq('attribute.type', $queryBuilder->expr()->literal($type)))
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
    }
}
