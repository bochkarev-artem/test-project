<?php
/**
 * @author Artyom Bochkarev
 */

namespace AppBundle\Service;

use AppBundle\Entity\Manufacturer;
use AppBundle\Entity\Product;
use AppBundle\Model\Importer\ImporterInterface;
use AppBundle\Model\Importer\ImportProduct;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Repository\RepositoryFactory;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ProductImporter
{
    /**
     * @var RepositoryFactory
     */
    protected $productRepo;

    /**
     * @var RepositoryFactory
     */
    protected $manufacturerRepo;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    /**
     * @var array
     */
    protected $persistedProductIds = [];

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em               = $em;
        $this->productRepo      = $this->em->getRepository('AppBundle:Product');
        $this->manufacturerRepo = $this->em->getRepository('AppBundle:Manufacturer');
        $this->accessor         = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param ImporterInterface $importer
     */
    public function import(ImporterInterface $importer)
    {
        $products = $importer->fetchAll();
        $this->iterate($importer, $products);
    }

    /**
     * @param ImporterInterface $importer
     * @param \Traversable      $products
     */
    protected function iterate($importer, $products)
    {
        $i = 1;
        foreach ($products as $importProduct) {
            $normalizedProduct = $importer->normalize($importProduct);
            $this->save($normalizedProduct);

            if (0 == $i++ % 100) {
                $this->persistedProductIds = [];
                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->em->flush();
        $this->em->clear();
    }

    /**
     * @param ImportProduct $importProduct
     */
    protected function save($importProduct)
    {
        $extId = $importProduct->getExternalId();

        if (in_array($extId, $this->persistedProductIds)) {
            $this->em->flush(); // Lets ensure that all products are in DB before searching in repository
        }

        if (!$product = $this->productRepo->findOneByExternalId($extId)) {
            $manufacturerTitle = $importProduct->getManufacturerTitle();
            if (!$manufacturer = $this->manufacturerRepo->findOneByTitle($manufacturerTitle)) {
                $manufacturer = new Manufacturer;
                $manufacturer->setTitle($manufacturerTitle);
                $this->em->persist($manufacturer);
                $this->em->flush();
            }

            $this->persistedProductIds[] = $extId;
            $product                     = new Product;
            $product
                ->setExternalId($extId)
                ->setTitle($importProduct->getTitle())
                ->setAvailability($importProduct->getAvailability())
                ->setDescription($importProduct->getDescription())
                ->setImage($importProduct->getImage())
                ->setPrice($importProduct->getPrice())
                ->setManufacturer($manufacturer)
            ;

            $this->em->persist($product);
        }
    }
}
