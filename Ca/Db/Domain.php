<?php
namespace Ca\Db;

/**
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Domain extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use \Uni\Db\Traits\InstitutionTrait;

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
        $this->modified = new \DateTime();
        $this->created = new \DateTime();

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
     * @param \DateTime $modified
     * @return Domain
     */
    public function setModified($modified) : Domain
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
     * @return Domain
     */
    public function setCreated($created) : Domain
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
        $errors = $this->validateInstitutionId($errors);

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        if (!$this->label) {
            $errors['label'] = 'Invalid value: label';
        }

        return $errors;
    }

}
