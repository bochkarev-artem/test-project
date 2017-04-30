<?php
/**
 * @author Artem Bochkarev
 */

namespace AppBundle\Provider;

use AppBundle\Entity\Product;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Type;
use FOS\ElasticaBundle\Provider\ProviderInterface;

class ProductProvider implements ProviderInterface
{
    /**
     * @var Type
     */
    private $productType;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var integer
     */
    private $batchSize;

    /**
     * @param Type            $productType
     * @param EntityManager   $em
     * @param integer         $batchSize
     */
    public function __construct(Type $productType, EntityManager $em, $batchSize)
    {
        $this->productType = $productType;
        $this->em          = $em;
        $this->batchSize   = $batchSize;
    }

    /**
     * {@inheritdoc}
     */
    public function populate(\Closure $loggerClosure = null, array $options = [])
    {
        $this->updateDocumentsByQuery($this->createQueryBuilder(), $loggerClosure);
        $this->em->clear();
    }

    /**
     * @return QueryBuilder
     */
    private function createQueryBuilder()
    {
        /* @var QueryBuilder $queryBuilder */
        $qb = $this->em->createQueryBuilder();

        return $qb
            ->select('p')
            ->from('AppBundle:Product', 'p')
            ;
    }

    /**
     * @param Product $product
     *
     * @return Document|boolean
     */
    private function prepareDocument(Product $product)
    {
        $productData = $this->collectData($product);
        if (empty($productData)) {
            return false;
        }

        return new Document($product->getId(), $productData, 'product', 'products');
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    private function collectData(Product $product)
    {
        $productData = [
            'id'           => $product->getId(),
            'external_id'  => $product->getExternalId(),
            'title'        => $product->getTitle(),
            'description'  => $product->getDescription(),
            'price'        => $product->getPrice(),
            'image'        => $product->getImage(),
            'path'         => $product->getPath(),
            'availability' => $product->getAvailability(),
        ];

        $productData = $this->collectManufacturerData($product, $productData);

        return $productData;
    }

    /**
     * @param Product  $product
     * @param array $productData
     *
     * @return array
     */
    private function collectManufacturerData(Product $product, $productData)
    {
        $manufacturer = $product->getManufacturer();
        $manufacturerData = [
            'manufacturer_id' => $manufacturer->getId(),
            'title'           => $manufacturer->getTitle(),
        ];

        $productData['manufacturer'] = $manufacturerData;

        return $productData;
    }

    /**
     * @param QueryBuilder  $queryBuilder
     * @param \Closure|null $loggerClosure
     *
     * @return bool
     */
    private function updateDocumentsByQuery(QueryBuilder $queryBuilder, \Closure $loggerClosure = null)
    {
        $nbObjects = $this->countObjects($queryBuilder);
        $products  = $this->getQueryIterator($queryBuilder);
        $documents = [];
        $processed = 0;
        $lastCount = 0;
        $stepStartTime = microtime(true);

        foreach ($products as $row) {
            /** @var Product $product */
            $product = array_shift($row);
            if ($document = $this->prepareDocument($product)) {
                array_push($documents, $document);
            } else {
                try {
                    $this->productType->deleteById($product->getId());
                }
                catch (NotFoundException $e) {}
            }

            $processed++;
            if ($processed % $this->batchSize === 0) {
                if ($loggerClosure) {
                    $stepNbObjects    = $processed - $lastCount;
                    $stepCount        = $processed;
                    $percentComplete  = 100 * $stepCount / $nbObjects;
                    $objectsPerSecond = $stepNbObjects / (microtime(true) - $stepStartTime);
                    $active           = round(memory_get_usage(true) / 1024 / 1024, 1);
                    $peak             = round(memory_get_peak_usage(true) / 1024 / 1024, 1);
                    $loggerClosure(
                        $stepCount,
                        $nbObjects,
                        "\n" . sprintf(
                            '%0.1f%% (%d/%d), %d objects/s %0.1fMb/%0.1fMb',
                            $percentComplete,
                            $stepCount,
                            $nbObjects,
                            $objectsPerSecond,
                            $active,
                            $peak
                        ) . "\n"
                    );
                }

                $this->productType->addDocuments($documents);
                $this->em->clear();

                $documents = [];
                $lastCount      = $processed;
                $stepStartTime  = microtime(true);
            }
        }

        if ($documents) {
            if ($loggerClosure) {
                $stepNbObjects    = $processed - $lastCount;
                $stepCount        = $processed;
                $percentComplete  = 100 * $stepCount / $nbObjects;
                $objectsPerSecond = $stepNbObjects / (microtime(true) - $stepStartTime);
                $active           = round(memory_get_usage(true) / 1024 / 1024, 1);
                $peak             = round(memory_get_peak_usage(true) / 1024 / 1024, 1);
                $loggerClosure(
                    $stepCount,
                    $nbObjects,
                    "\n" . sprintf(
                        '%0.1f%% (%d/%d), %d objects/s %0.1fMb/%0.1fMb',
                        $percentComplete,
                        $stepCount,
                        $nbObjects,
                        $objectsPerSecond,
                        $active,
                        $peak
                    ) . "\n"
                );
            }

            $this->productType->addDocuments($documents);
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
        } catch (QueryException $e) {
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

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return int
     */
    private function countObjects(QueryBuilder $queryBuilder)
    {
        $qb = clone $queryBuilder;

        $aliases = $qb->getRootAliases();
        $qb->select('COUNT(' . $aliases[0] . '.id)');

        return (integer) $qb->getQuery()->getSingleScalarResult();
    }
}
