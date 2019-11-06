<?php
namespace Ca\Db;

/**
 * @author Mick Mifsud
 * @created 2019-11-05
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Item extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{

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
    public $assessmentId = 0;

    /**
     * @var int
     */
    public $scaleId = 0;

    /**
     * @var int
     */
    public $domainId = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var bool
     */
    public $gradable = false;

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
     * Item
     */
    public function __construct()
    {
        $this->modified = new \DateTime();
        $this->created = new \DateTime();

    }
    
    /**
     * @param string $uid
     * @return Item
     */
    public function setUid($uid) : Item
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
     * @param int $assessmentId
     * @return Item
     */
    public function setAssessmentId($assessmentId) : Item
    {
        $this->assessmentId = $assessmentId;
        return $this;
    }

    /**
     * return int
     */
    public function getAssessmentId() : int
    {
        return $this->assessmentId;
    }

    /**
     * @param int $scaleId
     * @return Item
     */
    public function setScaleId($scaleId) : Item
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
     * @param int $domainId
     * @return Item
     */
    public function setDomainId($domainId) : Item
    {
        $this->domainId = $domainId;
        return $this;
    }

    /**
     * return int
     */
    public function getDomainId() : int
    {
        return $this->domainId;
    }

    /**
     * @param string $name
     * @return Item
     */
    public function setName($name) : Item
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
     * @return Item
     */
    public function setDescription($description) : Item
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
     * @param bool $gradable
     * @return Item
     */
    public function setGradable($gradable) : Item
    {
        $this->gradable = $gradable;
        return $this;
    }

    /**
     * return bool
     */
    public function getGradable() : bool
    {
        return $this->gradable;
    }

    /**
     * @param int $orderBy
     * @return Item
     */
    public function setOrderBy($orderBy) : Item
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
     * @return Item
     */
    public function setModified($modified) : Item
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
     * @return Item
     */
    public function setCreated($created) : Item
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

        if (!$this->assessmentId) {
            $errors['assessmentId'] = 'Invalid value: assessmentId';
        }

        if (!$this->scaleId) {
            $errors['scaleId'] = 'Invalid value: scaleId';
        }

//        if (!$this->name) {
//            $errors['name'] = 'Invalid value: name';
//        }


        return $errors;
    }

}
