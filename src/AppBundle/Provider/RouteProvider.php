<?php
/**
 * @author Artyom Bochkarev
 */

namespace AppBundle\Provider;

use AppBundle\Entity\Product;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use Elastica\Document;
use Elastica\Type;
use FOS\ElasticaBundle\Provider\ProviderInterface;

class RouteProvider implements ProviderInterface
{
    /**
     * @var Type
     */
    private $routeType;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var integer
     */
    private $batchSize;

    /**
     * @param Type          $routeType
     * @param EntityManager $em
     * @param integer       $batchSize
     */
    public function __construct(Type $routeType, EntityManager $em, $batchSize)
    {
        $this->routeType = $routeType;
        $this->em        = $em;
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function populate(\Closure $loggerClosure = null, array $options = [])
    {
        /* @var QueryBuilder $qb */
        $qb = $this->em->createQueryBuilder();
        $qb = $qb
            ->select('p')
            ->from("AppBundle:Product", 'p')
        ;

        $this->updateDocumentsByQuery($qb, $loggerClosure);
        $this->em->clear();
    }

    /**
     * @param Product $object
     *
     * @return array|bool
     */
    private function prepareDocuments($object)
    {
        $routes    = $this->collectObjectData($object);
        $documents = [];
        foreach ($routes as $routeId => $routeData) {
            array_push($documents, new Document($routeId, $routeData, 'route'));
        }

        return $documents;
    }

    /**
     * @param Product $object
     *
     * @return array
     */
    private function collectObjectData($object)
    {
        $className   = (new \ReflectionClass($object))->getShortName();
        $type        = strtolower($className);
        $objectId    = $object->getId();
        $routeParams = [
            'defaults' => [
                'id' => $objectId,
            ],
            'requirements' => [],
            'options'      => [],
        ];

        $routes = [];
        $routeId   = $type . ':' . $objectId;
        $routeData = [
            'params' => $routeParams,
            $type    => $objectId,
        ];

        $routeData['params']['defaults']['_controller'] = "AppBundle:Product:show$className";

        $routeData['path'] = $object->getPath();
        $routes[$routeId]  = $routeData;

        return $routes;
    }

    /**
     * @param QueryBuilder  $queryBuilder
     * @param \Closure|null $loggerClosure
     *
     * @return bool
     */
    private function updateDocumentsByQuery(QueryBuilder $queryBuilder, \Closure $loggerClosure = null)
    {
        $objects        = $this->getQueryIterator($queryBuilder);
        $documentsToAdd = [];
        $processed      = 0;

        foreach ($objects as $object) {
            if ($documents = $this->prepareDocuments(array_shift($object))) {
                $documentsToAdd = array_merge($documentsToAdd, $documents);
            }

            $processed++;
            if ($processed % $this->batchSize === 0) {
                $this->routeType->addDocuments($documentsToAdd);
                $this->em->clear();

                $documentsToAdd = [];
            }
        }

        if ($documentsToAdd) {
            $this->routeType->addDocuments($documentsToAdd);
            $this->em->clear();
        }

        return true;
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return IterableResult|array
     */
    protected function getQueryIterator(QueryBuilder $queryBuilder)
    {
        try {
            $objects = $queryBuilder->getQuery()->iterate();
        }
        catch (QueryException $e) {
            $aliases  = $queryBuilder->getRootAliases();
            $entities = $queryBuilder->getRootEntities();

            $idQb = clone $queryBuilder;
            $res  = $idQb
                ->select($aliases[0] . '.id')
                ->add('from', new Expr\From($entities[0], $aliases[0], $aliases[0] . '.id'), false)
                ->getQuery()
                ->getResult()
            ;

            $ids = array_keys($res);
            if (!$ids) {
                return [];
            }

            $newQb   = $this->em->createQueryBuilder();
            $objects = $newQb
                ->select($aliases[0])
                ->from($entities[0], $aliases[0], $aliases[0] . '.id')
                ->where($queryBuilder->expr()->in($aliases[0] . '.id', $ids))
                ->getQuery()
                ->iterate()
            ;
        }

        return $objects;
    }
}
