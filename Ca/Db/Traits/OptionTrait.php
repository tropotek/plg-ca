<?php
namespace Ca\Db\Traits;

use Ca\Db\Option;

/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait OptionTrait
{

    /**
     * @var Option
     */
    private $_option = null;


    /**
     * @param int $optionId
     * @return $this
     */
    public function setOptionId($optionId)
    {
        $this->optionId = (int)$optionId;
        return $this;
    }

    /**
     * return int
     */
    public function getOptionId() : int
    {
        return $this->optionId;
    }

    /**
     * Get the option related to this object
     *
     * @return Option|null
     */
    public function getOption()
    {
        if (!$this->_option) {
            try {
                $this->_option = \Ca\Db\OptionMap::create()->find($this->getOptionId());
            } catch (\Exception $e) {}
        }
        return $this->_option;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateOptionId($errors = [])
    {
        if (!$this->getOptionId()) {
            $errors['optionId'] = 'Invalid value: optionId';
        }
        return $errors;
    }

}