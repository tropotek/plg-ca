<?php
namespace Ca\Db\Traits;

use Ca\Db\Scale;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait ScaleTrait
{

    /**
     * @var Scale
     */
    private $_scale = null;


    /**
     * @param int $scaleId
     * @return $this
     */
    public function setScaleId($scaleId)
    {
        $this->scaleId = (int)$scaleId;
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
     * Get the scale related to this object
     *
     * @return Scale|null
     */
    public function getScale()
    {
        if (!$this->_scale) {
            try {
                $this->_scale = \Ca\Db\ScaleMap::create()->find($this->getScaleId());
            } catch (\Exception $e) {}
        }
        return $this->_scale;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateScaleId($errors = [])
    {
        if (!$this->getScaleId()) {
            $errors['scaleId'] = 'Invalid value: scaleId';
        }
        return $errors;
    }

}