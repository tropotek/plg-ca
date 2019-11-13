<?php
namespace Ca\Db;

/**
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Assessment extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use \App\Db\Traits\CourseTrait;

    const ASSESSOR_GROUP_STUDENT = 'student';
    const ASSESSOR_GROUP_COMPANY = 'company';
    //const ASSESSOR_GROUP_STAFF = 'staff';

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
    public $icon = 'fa fa-rebel';

    /**
     * @var array
     */
    public $placementStatus = array();

    /**
     * @var string
     */
    public $assessorGroup = 'student';


    /**
     * @var bool
     */
    public $includeZero = false;

    /**
     * @var string
     */
    public $description = '';

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
     * Assessment
     */
    public function __construct()
    {
        $this->modified = new \DateTime();
        $this->created = new \DateTime();

    }
    
    /**
     * @param string $uid
     * @return Assessment
     */
    public function setUid($uid) : Assessment
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
     * @return Assessment
     */
    public function setName($name) : Assessment
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
     * @param string $icon
     * @return Assessment
     */
    public function setIcon($icon) : Assessment
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * return string
     */
    public function getIcon() : string
    {
        return $this->icon;
    }

    /**
     * @param array $placementStatus
     * @return Assessment
     */
    public function setPlacementStatus(array $placementStatus) : Assessment
    {
        $this->placementStatus = $placementStatus;
        return $this;
    }

    /**
     * return array|null
     */
    public function getPlacementStatus() : ?array
    {
        return $this->placementStatus;
    }

    /**
     * @param string $assessorGroup
     * @return Assessment
     */
    public function setAssessorGroup($assessorGroup) : Assessment
    {
        $this->assessorGroup = $assessorGroup;
        return $this;
    }

    /**
     * return string
     */
    public function getAssessorGroup() : string
    {
        return $this->assessorGroup;
    }

    /**
     * @param bool $includeZero
     * @return Assessment
     */
    public function setIncludeZero($includeZero) : Assessment
    {
        $this->includeZero = $includeZero;
        return $this;
    }

    /**
     * return bool
     */
    public function isIncludeZero() : bool
    {
        return $this->includeZero;
    }

    /**
     * @param $subjectId
     * @param null|\DateTime $publishResult
     * @return Assessment
     */
    public function setPublishResult($subjectId, $publishResult) : Assessment
    {
        AssessmentMap::create()->setPublishStudent($subjectId, $this->getId(), $publishResult);
        return $this;
    }

    /**
     * return null|\DateTime
     * @param int $subjectId
     * @return null|\DateTime
     */
    public function getPublishResult($subjectId) : ?\DateTime
    {
        return AssessmentMap::create()->getPublishStudent($subjectId, $this->getId());
    }

    /**
     * @param string $description
     * @return Assessment
     */
    public function setDescription($description) : Assessment
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
     * @param string $notes
     * @return Assessment
     */
    public function setNotes($notes) : Assessment
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
     * @return Assessment
     */
    public function setModified($modified) : Assessment
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
     * @return Assessment
     */
    public function setCreated($created) : Assessment
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
     * When an assessment is active for a subject staff and companies can access entries
     *
     * @param int $subjectId
     * @return bool
     */
    public function isActive($subjectId)
    {
        if ($subjectId instanceof \Uni\Db\SubjectIface) $subjectId = $subjectId->getId();
        return AssessmentMap::create()->hasSubject($subjectId, $this->getId());
    }

    /**
     * When an assessment is published, students can submit self-assessments
     * and also view any assessment entries that have been completed/approved including self-assessments.
     *
     * @param $subjectId
     * @return bool
     */
    public function isPublished($subjectId)
    {
        if ($subjectId instanceof \Uni\Db\SubjectIface) $subjectId = $subjectId->getId();
        return AssessmentMap::create()->hasSubject($subjectId, $this->getId());
    }

    /**
     * Use this to test if the public user or student can submit/view an entry
     *
     * @param \App\Db\Placement $placement (optional)
     * @return bool
     */
    public function isAvailable($placement = null)
    {
        if (!$this->getId() || !$this->isActive($placement->getSubjectId())) return false;
        $b = true;
        if ($placement) {
            $b &= in_array($placement->getStatus(), $this->getPlacementStatus());
            $b &= AssessmentMap::create()->hasPlacementType($this->getId(), $placement->getPlacementTypeId());
        }
        return $b;
    }

    /**
     * If the assessor group is student then this is a self assessment
     * Self assessment forms should be shown after the student submit their
     *
     * @return bool
     */
    public function isSelfAssessment()
    {
        return ($this->getAssessorGroup() == self::ASSESSOR_GROUP_STUDENT);
    }

    /**
     * return the status list for a select field
     * @param null|string $current
     * @return array
     */
    public static function getAssessorGroupList($current = null)
    {
        $arr = \Tk\Form\Field\Select::arrayToSelectList(\Tk\ObjectUtil::getClassConstants(__CLASS__, 'ASSESSOR_GROUP'));
        if (is_string($current)) {
            $arr2 = array();
            foreach ($arr as $k => $v) {
                if ($v == $current) {
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
        $errors = $this->validateCourseId($errors);

        if (!$this->getName()) {
            $errors['name'] = 'Invalid value: name';
        }

        if (!$this->getIcon()) {
            $errors['icon'] = 'Invalid value: icon';
        }

        if (!$this->isSelfAssessment() && (!$this->getPlacementStatus() || !count($this->getPlacementStatus()))) {
            $errors['statusAvailable'] = 'Invalid value: statusAvailable';
        }

        if (!$this->getAssessorGroup()) {
            $errors['assessorGroup'] = 'Invalid value: assessorGroup';
        }

        return $errors;
    }

}
