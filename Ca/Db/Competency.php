<?php
namespace Ca\Db;

/**
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Competency extends \Tk\Db\Map\Model implements \Tk\ValidInterface
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
    public $courseId = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * Competency
     */
    public function __construct()
    {
        $this->modified = new \DateTime();
        $this->created = new \DateTime();

    }
    
    /**
     * @param string $uid
     * @return Competency
     */
    public function setUid($uid) : Competency
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
     * @param int $courseId
     * @return Competency
     */
    public function setCourseId($courseId) : Competency
    {
        $this->courseId = $courseId;
        return $this;
    }

    /**
     * return int
     */
    public function getCourseId() : int
    {
        return $this->courseId;
    }

    /**
     * @param string $name
     * @return Competency
     */
    public function setName($name) : Competency
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
     * @return Competency
     */
    public function setDescription($description) : Competency
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
     * @param \DateTime $modified
     * @return Competency
     */
    public function setModified($modified) : Competency
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
     * @return Competency
     */
    public function setCreated($created) : Competency
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

        if (!$this->courseId) {
            $errors['courseId'] = 'Invalid value: courseId';
        }

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        return $errors;
    }

}
