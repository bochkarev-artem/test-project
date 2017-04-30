<?php
/**
 * @author Artem Bochkarev
 */

namespace AppBundle\Model\Importer;

interface ImporterInterface
{
    /**
     * @return \Traversable
     */
    function fetchAll();

    /**
     * @param mixed $product
     *
     * @return ImportProduct
     */
    function normalize($product);
}
