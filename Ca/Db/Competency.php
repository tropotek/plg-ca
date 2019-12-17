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
class Competency extends \Tk\Db\Map\Model implements \Tk\ValidInterface
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
        $this->_TimestampTrait();

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
     * @return array
     */
    public function validate()
    {
        $errors = array();
        $errors = $this->validateInstitutionId($errors);

        if (!$this->getName()) {
            $errors['name'] = 'Invalid value: name';
        }

        return $errors;
    }

}
