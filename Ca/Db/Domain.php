<?php
namespace Ca\Db;

use Bs\Db\Traits\TimestampTrait;
use Uni\Db\Traits\InstitutionTrait;

/**
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Domain extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use InstitutionTrait;
    use TimestampTrait;

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var string
     */
    public $uid = '';

    /**
     * @var int
     */
    public $institutionId = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $label = '';

    /**
     * @var int
     */
    public $orderBy = 0;

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * Domain
     */
    public function __construct()
    {
        $this->_TimestampTrait();

    }
    
    /**
     * @param string $uid
     * @return Domain
     */
    public function setUid($uid) : Domain
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * return string
     */
    public function getUid() : string
    {
        return $this->uid;
    }

    /**
     * @param string $name
     * @return Domain
     */
    public function setName($name) : Domain
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
     * @return Domain
     */
    public function setDescription($description) : Domain
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
     * @param string $label
     * @return Domain
     */
    public function setLabel($label) : Domain
    {
        $this->label = $label;
        return $this;
    }

    /**
     * return string
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @param int $orderBy
     * @return Domain
     */
    public function setOrderBy($orderBy) : Domain
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * return int
     */
    public function getOrderBy() : int
    {
        return $this->orderBy;
    }
    
    /**
     * @return array
     */
    public function validate()
    {
        $errors = array();
        $errors = $this->validateInstitutionId($errors);

        if (!$this->getName()) {
            $errors['name'] = 'Invalid value: name';
        }

        if (!$this->getLabel()) {
            $errors['label'] = 'Invalid value: label';
        }

        return $errors;
    }

}
