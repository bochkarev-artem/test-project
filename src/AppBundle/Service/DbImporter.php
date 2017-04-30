<?php
/**
 * @author Artyom Bochkarev
 */

namespace AppBundle\Service;

use AppBundle\Model\Importer\ImporterInterface;
use AppBundle\Model\Importer\ImportProduct;
use Doctrine\DBAL\Connection;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\DBAL\Driver\Statement;

class DbImporter implements ImporterInterface
{
    /**
     * @var array
     */
    protected $fieldMapping;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @param Connection $connection
     * @param string     $tableName
     */
    public function __construct(Connection $connection, $tableName)
    {
        $this->connection   = $connection;
        $this->tableName    = $tableName;
        $this->fieldMapping = [
            'id'            => 'externalId',
            'name'          => 'title',
            'additional'    => 'description',
            'price'         => 'price',
            'availability'  => 'availability',
            'product_image' => 'image',
            'manufacturer'  => 'manufacturerTitle',
        ];
    }

    /**
     * @return \Traversable|Statement|int
     */
    public function fetchAll()
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('*')
            ->from($this->tableName)
        ;

        return $qb->execute();
    }

    /**
     * @param array $product
     *
     * @return ImportProduct
     */
    public function normalize($product)
    {
        $accessor      = PropertyAccess::createPropertyAccessor();
        $importProduct = new ImportProduct;

        foreach ($product as $key => $value) {
            $accessor->setValue($importProduct, $this->fieldMapping[$key], $value);
        }

        return $importProduct;
    }
}
