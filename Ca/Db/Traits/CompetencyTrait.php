<?php
namespace Ca\Db\Traits;

use Ca\Db\Competency;

/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait CompetencyTrait
{

    /**
     * @var Competency
     */
    private $_competency = null;


    /**
     * @param int $competencyId
     * @return $this
     */
    public function setCompetencyId($competencyId)
    {
        $this->competencyId = (int)$competencyId;
        return $this;
    }

    /**
     * return int
     */
    public function getCompetencyId() : int
    {
        return $this->competencyId;
    }

    /**
     * Get the competency related to this object
     *
     * @return Competency|null
     */
    public function getCompetency()
    {
        if (!$this->_competency) {
            try {
                $this->_competency = \Ca\Db\CompetencyMap::create()->find($this->getCompetencyId());
            } catch (\Exception $e) {}
        }
        return $this->_competency;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateCompetencyId($errors = [])
    {
        if (!$this->getCompetencyId()) {
            $errors['competencyId'] = 'Invalid value: competencyId';
        }
        return $errors;
    }

}