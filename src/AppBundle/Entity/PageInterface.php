<?php
/**
 * @author Artyom Bochkarev
 */

namespace AppBundle\Entity;

/**
 * Interface PageInterface
 * @package AppBundle\Entity
 */
interface PageInterface
{
    /**
     * @return string
     */
    public function getPathPrefix();

    /**
     * @return string
     */
    public function getPath();
}
