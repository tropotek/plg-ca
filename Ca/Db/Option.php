<?php
namespace Ca\Db;

/**
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Option extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $scaleId = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var float
     */
    public $value = 0;

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * Option
     */
    public function __construct()
    {
        $this->modified = new \DateTime();
        $this->created = new \DateTime();

    }
    
    /**
     * @param int $scaleId
     * @return Option
     */
    public function setScaleId($scaleId) : Option
    {
        $this->scaleId = $scaleId;
        return $this;
    }

    /**
     * return int
     */
    public function getScaleId() : int
    {
        return $this->scaleId;
    }

    /**
     * @param string $name
     * @return Option
     */
    public function setName($name) : Option
    {
        $this->name = $name;
        return $this;
    }

    /**
     * return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $description
     * @return Option
     */
    public function setDescription($description) : Option
    {
        $this->description = $description;
        return $this;
    }

    /**
     * return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @param float $value
     * @return Option
     */
    public function setValue($value) : Option
    {
        $this->value = $value;
        return $this;
    }

    /**
     * return float
     */
    public function getValue() : float
    {
        return $this->value;
    }

    /**
     * @param \DateTime $modified
     * @return Option
     */
    public function setModified($modified) : Option
    {
        $this->modified = $modified;
        return $this;
    }

    /**
     * return \DateTime
     */
    public function getModified() : \DateTime
    {
        return $this->modified;
    }

    /**
     * @param \DateTime $created
     * @return Option
     */
    public function setCreated($created) : Option
    {
        $this->created = $created;
        return $this;
    }

    /**
     * return \DateTime
     */
    public function getCreated() : \DateTime
    {
        return $this->created;
    }
    
    /**
     * @return array
     */
    public function validate()
    {
        $errors = array();

        if (!$this->scaleId) {
            $errors['scaleId'] = 'Invalid value: scaleId';
        }

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        if (!$this->value) {
            $errors['value'] = 'Invalid value: value';
        }

        return $errors;
    }

}
