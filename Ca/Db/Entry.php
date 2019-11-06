<?php
namespace Ca\Db;

use Tk\Form\Field\Select;
use Tk\ObjectUtil;

/**
 * @author Mick Mifsud
 * @created 2019-11-06
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Entry extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_NOT_APPROVED = 'not approved';

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $assessmentId = 0;

    /**
     * @var int
     */
    public $subjectId = 0;

    /**
     * @var int
     */
    public $studentId = 0;

    /**
     * @var int
     */
    public $assessorId = 0;

    /**
     * @var int
     */
    public $placementId = 0;

    /**
     * @var string
     */
    public $title = '';

    /**
     * @var string
     */
    public $assessorName = '';

    /**
     * @var string
     */
    public $assessorEmail = '';

    /**
     * @var int
     */
    public $absent = 0;

    /**
     * @var float
     */
    public $average = 0;

    /**
     * @var string
     */
    public $status = 'pending';

    /**
     * @var string
     */
    public $notes = '';

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * Entry
     */
    public function __construct()
    {
        $this->modified = new \DateTime();
        $this->created = new \DateTime();

    }
    
    /**
     * @param int $assessmentId
     * @return Entry
     */
    public function setAssessmentId($assessmentId) : Entry
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
     * @param int $subjectId
     * @return Entry
     */
    public function setSubjectId($subjectId) : Entry
    {
        $this->subjectId = $subjectId;
        return $this;
    }

    /**
     * return int
     */
    public function getSubjectId() : int
    {
        return $this->subjectId;
    }

    /**
     * @param int $studentId
     * @return Entry
     */
    public function setStudentId($studentId) : Entry
    {
        $this->studentId = $studentId;
        return $this;
    }

    /**
     * return int
     */
    public function getStudentId() : int
    {
        return $this->studentId;
    }

    /**
     * @param int $assessorId
     * @return Entry
     */
    public function setAssessorId($assessorId) : Entry
    {
        $this->assessorId = $assessorId;
        return $this;
    }

    /**
     * return int
     */
    public function getAssessorId() : int
    {
        return $this->assessorId;
    }

    /**
     * @param int $placementId
     * @return Entry
     */
    public function setPlacementId($placementId) : Entry
    {
        $this->placementId = $placementId;
        return $this;
    }

    /**
     * return int
     */
    public function getPlacementId() : int
    {
        return $this->placementId;
    }

    /**
     * @param string $title
     * @return Entry
     */
    public function setTitle($title) : Entry
    {
        $this->title = $title;
        return $this;
    }

    /**
     * return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @param string $assessorName
     * @return Entry
     */
    public function setAssessorName($assessorName) : Entry
    {
        $this->assessorName = $assessorName;
        return $this;
    }

    /**
     * return string
     */
    public function getAssessorName() : string
    {
        return $this->assessorName;
    }

    /**
     * @param string $assessorEmail
     * @return Entry
     */
    public function setAssessorEmail($assessorEmail) : Entry
    {
        $this->assessorEmail = $assessorEmail;
        return $this;
    }

    /**
     * return string
     */
    public function getAssessorEmail() : string
    {
        return $this->assessorEmail;
    }

    /**
     * @param int $absent
     * @return Entry
     */
    public function setAbsent($absent) : Entry
    {
        $this->absent = $absent;
        return $this;
    }

    /**
     * return int
     */
    public function getAbsent() : int
    {
        return $this->absent;
    }

    /**
     * @param float $average
     * @return Entry
     */
    public function setAverage($average) : Entry
    {
        $this->average = $average;
        return $this;
    }

    /**
     * return float
     */
    public function getAverage() : float
    {
        return $this->average;
    }

    /**
     * @param string $status
     * @return Entry
     */
    public function setStatus($status) : Entry
    {
        $this->status = $status;
        return $this;
    }

    /**
     * return string
     */
    public function getStatus() : string
    {
        return $this->status;
    }

    /**
     * @param string $notes
     * @return Entry
     */
    public function setNotes($notes) : Entry
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * return string
     */
    public function getNotes() : string
    {
        return $this->notes;
    }

    /**
     * @param \DateTime $modified
     * @return Entry
     */
    public function setModified($modified) : Entry
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
     * @return Entry
     */
    public function setCreated($created) : Entry
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
     * return the status list for a select field
     * @param null|string $status
     * @return array
     */
    public static function getStatusList($status = null)
    {
        $arr = Select::arrayToSelectList(ObjectUtil::getClassConstants(__CLASS__, 'STATUS'));
        if (is_string($status)) {
            $arr2 = array();
            foreach ($arr as $k => $v) {
                if ($v == $status) {
                    $arr2[$k.' (Current)'] = $v;
                } else {
                    $arr2[$k] = $v;
                }
            }
            $arr = $arr2;
        }
        return $arr;
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

        if (!$this->subjectId) {
            $errors['subjectId'] = 'Invalid value: subjectId';
        }

        if (!$this->studentId) {
            $errors['studentId'] = 'Invalid value: studentId';
        }

//        if (!$this->assessorId) {
//            $errors['assessorId'] = 'Invalid value: assessorId';
//        }

//        if (!$this->placementId) {
//            $errors['placementId'] = 'Invalid value: placementId';
//        }

        if (!$this->title) {
            $errors['title'] = 'Invalid value: title';
        }

        if (!$this->assessorName) {
            $errors['assessorName'] = 'Invalid value: assessorName';
        }

        if (!$this->assessorEmail) {
            $errors['assessorEmail'] = 'Invalid value: assessorEmail';
        }

//        if (!$this->absent) {
//            $errors['absent'] = 'Invalid value: absent';
//        }

//        if (!$this->average) {
//            $errors['average'] = 'Invalid value: average';
//        }

        if (!$this->status) {
            $errors['status'] = 'Invalid value: status';
        }

        return $errors;
    }

}
