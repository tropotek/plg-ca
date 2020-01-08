<?php
namespace Ca\Db;

use App\Db\Traits\PlacementTrait;
use Bs\Db\Traits\TimestampTrait;
use Ca\Db\Traits\AssessmentTrait;
use Tk\Form\Field\Select;
use Tk\ObjectUtil;
use Uni\Config;
use Uni\Db\Traits\StatusTrait;
use Uni\Db\Traits\SubjectTrait;

/**
 * @author Mick Mifsud
 * @created 2019-11-06
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Entry extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use AssessmentTrait;
    use SubjectTrait;
    use PlacementTrait;
    use TimestampTrait;
    use StatusTrait;

    const STATUS_PENDING        = 'pending';
    const STATUS_APPROVED       = 'approved';
    const STATUS_AMEND          = 'amend';
    const STATUS_NOT_APPROVED   = 'not approved';

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
    public $status = self::STATUS_PENDING;

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
     * @var null|\Uni\Db\UserIface
     */
    private $_student = null;

    /**
     * @var null|\Uni\Db\UserIface
     */
    private $_assessor = null;


    /**
     * Entry
     */
    public function __construct()
    {
        $this->_TimestampTrait();

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
     * @return \Tk\Db\Map\Model|\Tk\Db\ModelInterface|null|\Uni\Db\UserIface
     */
    public function getStudent()
    {
        if (!$this->_student) {
            try {
                $this->_student = Config::getInstance()->getUserMapper()->find($this->getStudentId());
            } catch (\Exception $e) {}
        }
        return $this->_student;
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
     * @return \Tk\Db\Map\Model|\Tk\Db\ModelInterface|null|\Uni\Db\UserIface
     */
    public function getAssessor()
    {
        if (!$this->_assessor) {
            try {
                $this->_assessor = Config::getInstance()->getUserMapper()->find($this->getAssessorId());
            } catch (\Exception $e) {}
        }
        return $this->_assessor;
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
        if ($this->average == 0)
            $this->calculateAverage();
        return $this->average;
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
     * Get the entry average score.
     *
     * @return float
     * @todo We need to complete this function
     */
    public function calculateAverage()
    {
        return 0.0;
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
        $errors = $this->validateAssessmentId($errors);
        $errors = $this->validateSubjectId($errors);

        if (!$this->getStudentId()) {
            $errors['studentId'] = 'Invalid Student ID';
        }

        if (!$this->getTitle()) {
            $errors['title'] = 'Invalid Title';
        }

        if (!$this->getAssessorName()) {
            $errors['assessorName'] = 'Invalid Assessor Name';
        }

        if (!filter_var($this->getAssessorEmail(), FILTER_VALIDATE_EMAIL)) {
            $errors['assessorEmail'] = 'Invalid Assessor Email';
        }

//        if ($this->getAssessorEmail() && !filter_var($this->getAssessorEmail(), FILTER_VALIDATE_EMAIL)) {
//            $errors['assessorEmail'] = 'Invalid Assessor Email';
//        }

        if (!$this->getStatus()) {
            $errors['status'] = 'Invalid Status value';
        }

        return $errors;
    }

}
