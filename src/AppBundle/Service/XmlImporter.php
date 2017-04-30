<?php
/**
 * @author Artyom Bochkarev
 */

namespace AppBundle\Service;

use AppBundle\Model\Importer\ImporterInterface;
use AppBundle\Model\Importer\ImportProduct;
use Symfony\Component\PropertyAccess\PropertyAccess;

class XmlImporter implements ImporterInterface
{
    /**
     * @var array
     */
    protected $fieldMapping;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * @param string $endpoint
     */
    public function __construct($endpoint)
    {
        $this->endpoint     = $endpoint;
        $this->fieldMapping = [
            'title'        => ['path' => 'title', 'type' => 'string'],
            'additional'   => ['path' => 'description', 'type' => 'string'],
            'price'        => ['path' => 'price', 'type' => 'float'],
            'availability' => ['path' => 'availability', 'type' => 'int'],
            'image'        => ['path' => 'image', 'type' => 'string'],
            'manufacturer' => ['path' => 'manufacturerTitle', 'type' => 'string'],
        ];
    }

    /**
     * @return \Traversable
     */
    public function fetchAll()
    {
        return $this->getXml($this->endpoint);
    }

    /**
     * @param \SimpleXMLElement $product
     *
     * @return ImportProduct
     */
    public function normalize($product)
    {
        $accessor      = PropertyAccess::createPropertyAccessor();
        $importProduct = new ImportProduct;

        $importProduct->setExternalId((integer) $product['id']);
        foreach ($product as $key => $value) {
            switch ($this->fieldMapping[$key]['type']) {
                case 'string':
                    $value = (string) $product->{"$key"};
                    break;
                case 'int':
                    $value = (integer) $product->{"$key"};
                    break;
                case 'float':
                    $value = (float) $product->{"$key"};
                    break;
            }

            $accessor->setValue($importProduct, $this->fieldMapping[$key]['path'], $value);
        }

        return $importProduct;
    }

    /**
     * @param string $endpoint
     *
     * @return \SimpleXMLElement
     */
    private function getXml($endpoint)
    {
        $content = file_get_contents($endpoint);

        return simplexml_load_string($content);
    }
}
