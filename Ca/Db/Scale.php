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
    use \Uni\Db\Traits\InstitutionTrait;

    const TYPE_TEXT = 'text';
    const TYPE_VALUE = 'value';
    const TYPE_CHOICE = 'choice';

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
    public $maxValue = 0;

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
        $this->institutionId = $this->getConfig()->getInstitutionId();
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
    public function isMultiple() : bool
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
     * @param float $maxValue
     * @return Scale
     */
    public function setMaxValue($maxValue) : Scale
    {
        $this->maxValue = $maxValue;
        return $this;
    }

    /**
     * return float
     */
    public function getMaxValue() : float
    {
        return $this->maxValue;
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
        $errors = $this->validateInstitutionId($errors);

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        if (!$this->type) {
            $errors['type'] = 'Invalid value: type';
        }

        if ($this->type == self::TYPE_CHOICE && $this->getMultiple() && !$this->calcType) {
            $errors['calcType'] = 'Invalid value: calcType';
        }

        if ($this->type == self::TYPE_VALUE && !$this->maxValue) {
            $errors['maxValue'] = 'Invalid value: maxValue';
        }

        return $errors;
    }

}
