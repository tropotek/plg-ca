<?php
namespace Ca\Db\Traits;

use Ca\Db\Assessment;

/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait AssessmentTrait
{

    /**
     * @var Assessment
     */
    private $_assessment = null;


    /**
     * @param int $assessmentId
     * @return $this
     */
    public function setAssessmentId($assessmentId)
    {
        $this->assessmentId = (int)$assessmentId;
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
     * Get the assessment related to this object
     *
     * @return Assessment|null
     */
    public function getAssessment()
    {
        if (!$this->_assessment) {
            try {
                $this->_assessment = \Ca\Db\AssessmentMap::create()->find($this->getAssessmentId());
            } catch (\Exception $e) {}
        }
        return $this->_assessment;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateAssessmentId($errors = [])
    {
        if (!$this->getAssessmentId()) {
            $errors['assessmentId'] = 'Invalid value: assessmentId';
        }
        return $errors;
    }

}