<?php
namespace Ca\Db;

/**
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Scale extends \Tk\Db\Map\Model implements \Tk\ValidInterface
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
    public $type = 'text';

    /**
     * @var bool
     */
    public $multiple = false;

    /**
     * @var string
     */
    public $calcType = 'avg';

    /**
     * @var float
     */
    public $maxScore = 0;

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * Scale
     */
    public function __construct()
    {
        $this->modified = new \DateTime();
        $this->created = new \DateTime();

    }
    
    /**
     * @param string $uid
     * @return Scale
     */
    public function setUid($uid) : Scale
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
     * @param int $institutionId
     * @return Scale
     */
    public function setInstitutionId($institutionId) : Scale
    {
        $this->institutionId = $institutionId;
        return $this;
    }

    /**
     * return int
     */
    public function getInstitutionId() : int
    {
        return $this->institutionId;
    }

    /**
     * @param string $name
     * @return Scale
     */
    public function setName($name) : Scale
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
     * @return Scale
     */
    public function setDescription($description) : Scale
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
     * @param string $type
     * @return Scale
     */
    public function setType($type) : Scale
    {
        $this->type = $type;
        return $this;
    }

    /**
     * return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @param bool $multiple
     * @return Scale
     */
    public function setMultiple($multiple) : Scale
    {
        $this->multiple = $multiple;
        return $this;
    }

    /**
     * return bool
     */
    public function getMultiple() : bool
    {
        return $this->multiple;
    }

    /**
     * @param string $calcType
     * @return Scale
     */
    public function setCalcType($calcType) : Scale
    {
        $this->calcType = $calcType;
        return $this;
    }

    /**
     * return string
     */
    public function getCalcType() : string
    {
        return $this->calcType;
    }

    /**
     * @param float $maxScore
     * @return Scale
     */
    public function setMaxScore($maxScore) : Scale
    {
        $this->maxScore = $maxScore;
        return $this;
    }

    /**
     * return float
     */
    public function getMaxScore() : float
    {
        return $this->maxScore;
    }

    /**
     * @param \DateTime $modified
     * @return Scale
     */
    public function setModified($modified) : Scale
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
     * @return Scale
     */
    public function setCreated($created) : Scale
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

        if (!$this->uid) {
            $errors['uid'] = 'Invalid value: uid';
        }

        if (!$this->institutionId) {
            $errors['institutionId'] = 'Invalid value: institutionId';
        }

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        if (!$this->type) {
            $errors['type'] = 'Invalid value: type';
        }

        if (!$this->calcType) {
            $errors['calcType'] = 'Invalid value: calcType';
        }

        if (!$this->maxScore) {
            $errors['maxScore'] = 'Invalid value: maxScore';
        }

        return $errors;
    }

}
