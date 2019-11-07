<?php
namespace Ca\Db\Traits;

use Ca\Db\Domain;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait DomainTrait
{

    /**
     * @var Domain
     */
    private $_domain = null;


    /**
     * @param int $domainId
     * @return $this
     */
    public function setDomainId($domainId)
    {
        $this->domainId = (int)$domainId;
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
     * Get the domain related to this object
     *
     * @return Domain|null
     */
    public function getDomain()
    {
        if (!$this->_domain) {
            try {
                $this->_domain = \Ca\Db\DomainMap::create()->find($this->getDomainId());
            } catch (\Exception $e) {}
        }
        return $this->_domain;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateDomainId($errors = [])
    {
        if (!$this->getDomainId()) {
            $errors['domainId'] = 'Invalid value: domainId';
        }
        return $errors;
    }

}