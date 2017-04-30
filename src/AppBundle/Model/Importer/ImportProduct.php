<?php
/**
 * @author Artyom Bochkarev
 */

namespace AppBundle\Model\Importer;

class ImportProduct
{
    /**
     * @var integer
     */
    private $externalId;

    /**
     * @var string
     */
    private $manufacturerTitle;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var float
     */
    private $price;

    /**
     * @var integer
     */
    private $availability;

    /**
     * @var string
     */
    private $image;

    /**
     * @return int
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @param int $externalId
     *
     * @return ImportProduct
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * @return string
     */
    public function getManufacturerTitle()
    {
        return $this->manufacturerTitle;
    }

    /**
     * @param string $manufacturerTitle
     *
     * @return ImportProduct
     */
    public function setManufacturerTitle($manufacturerTitle)
    {
        $this->manufacturerTitle = $manufacturerTitle;

        return $this;
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
     * @return ImportProduct
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return ImportProduct
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return ImportProduct
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return int
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * @param int $availability
     *
     * @return ImportProduct
     */
    public function setAvailability($availability)
    {
        $this->availability = $availability;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     *
     * @return ImportProduct
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }
}
