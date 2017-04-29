<?php
/**
 * @author Artyom Bochkarev
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\Manufacturer
 *
 * @ORM\Entity
 * @ORM\Table(name="manufacturer")
 */
class Manufacturer
{
    /**
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="manufacturer_id", type="integer")
     */
    private $id;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string")
     */
    private $title;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Manufacturer
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}
