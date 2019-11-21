<?php
namespace Ca\Listener;

use Tk\Event\Subscriber;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StudentAssessmentHandler implements Subscriber
{

    /**
     * @param \Tk\Event\Event $event
     * @throws \Exception
     */
    public function addCheckColumns(\Tk\Event\Event $event)
    {
        /** @var \App\Ui\StudentAssessment $studentAssessment */
        $studentAssessment = $event->get('studentAssessment');
        $subject = $studentAssessment->getSubject();

        /** @var \App\Db\Placement $placement */
        foreach($studentAssessment->getPlacementList() as $placement) {
            $report = $placement->getReport();
            $list = \Ca\Db\AssessmentMap::create()->findFiltered(array(
                'subjectId' => $subject->getId(),
                'placementTypeId' => $placement->getPlacementTypeId()
            ), \Tk\Db\Tool::create('FIELD(`name`, \'Self Assessment\') DESC'));
            foreach($list as $assessment) {
                /** @var \Ca\Db\Entry $entry */
                $entry = \Ca\Db\EntryMap::create()->findFiltered(array(
                    'assessmentId' => $assessment->getId(),
                    'placementId' => $placement->getId(),
                    'status' => array(\Ca\Db\Entry::STATUS_APPROVED, \Ca\Db\Entry::STATUS_PENDING, \Ca\Db\Entry::STATUS_AMEND)
                ))->current();
                $css = '';
                if ($entry) {
                    switch ($entry->getStatus()) {
                        case \Ca\Db\Entry::STATUS_PENDING:
                        case \Ca\Db\Entry::STATUS_AMEND:
                            $css = 'text-default';
                    }
                    $studentAssessment->addCheckColumn($assessment->getName(), $placement->getId(), ($entry != null), $css, $entry->getStatus());
                };
            }
        }
    }

    /**
     * getSubscribedEvents
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \App\UiEvents::STUDENT_ASSESSMENT_INIT => array(array('addCheckColumns', 0))
        );
    }

}


