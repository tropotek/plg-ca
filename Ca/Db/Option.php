<?php
namespace Ca\Db;

use Bs\Db\Traits\TimestampTrait;

/**
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Option extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use Traits\ScaleTrait;
    use TimestampTrait;

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
        $this->_TimestampTrait();

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
     * @return array
     */
    public function validate()
    {
        $errors = array();
        $errors = $this->validateScaleId($errors);

        if (!$this->getName()) {
            $errors['name'] = 'Invalid value: name';
        }
        return $errors;
    }

}
