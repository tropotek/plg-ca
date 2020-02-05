<?php
namespace Ca\Listener;

use Symfony\Component\Console\Output\Output;
use Tk\ConfigTrait;
use Tk\Event\Event;
use Tk\Event\Subscriber;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 *
 * @todo See todo.md as this need to be refactored to be a more maintainable codebase
 *
 */
class CronHandler implements Subscriber
{
    use ConfigTrait;


    /**
     * @param Event $event
     * @throws \Exception
     * @deprecated
     */
    public function onCron(Event $event)
    {
        /** @var \App\Console\Cron $console */
        $console = $event->get('console');

        $this->sendReminders($console);
        $console->write(' - Assessment Reminders Complete!');
    }


    /**
     * @TODO: Need to search for all placements with outstanding evaluations????
     * @TODO:  1. Create some assessment settings that have a firstReminder and recurringReminder and recurringMax
     * @TODO:  2. This should be placed into the competency Assessment plugin tho? Unless we create a EMS reminder system?
     *
     * @note: This will only find placements without an evaluation record.
     * @note: It cannot find entries that are in a status as that does not
     * @note:   make logical sense and confuses the business logic trying to be achieved.
     *
     * @param \App\Console\Cron $console
     * @throws \Exception
     */
    protected function sendReminders($console)
    {
        $console->write(' - Sending Assessment Reminders: ');

        // Placement Evaluating reminder after 7 days, then send every 28 days, for max 4 months.

        $console->writeBlue('    - TODO: Send initial assessment reminder');
        $console->writeBlue('    - TODO: Send any outstanding reminders less than max number of times.');
        $console->writeBlue('    - TODO: For any reminders on their last send, also email the subject coordinator informing them of the issue.');
        $console->write(' ');

        // Get enabled courses
        $plugin = \Ca\Plugin::getInstance();
        $courseList = $plugin->getPluginFactory()->getPluginZoneIdList($plugin->getName(), \App\Plugin\Iface::ZONE_COURSE);

        foreach ($courseList as $courseData) {
            /** @var \Uni\Db\CourseIface $course */
            $course = $this->getConfig()->getCourseMapper()->find($courseData->zone_id);
            if (!$course) continue;
            $assessmentList = \Ca\Db\AssessmentMap::create()->findFiltered(array('courseId' => $courseData->zone_id));
            if (!$assessmentList->countAll()) continue;

            $console->write('    Course: ' . $course->getName());
            $subjectList = $this->getConfig()->getSubjectMapper()->findFiltered(array('courseId' => $course->getId(), 'active' => true));
            foreach ($subjectList as $subject) {
                $console->write('      Subject: ' . $subject->getName());
                foreach ($assessmentList as $assessment) {
                    $console->writeComment('        Assess: ' . $assessment->getName() . ' [' . $assessment->getId() . ']');
                    $placementTypeIds = $assessment->getPlacementTypes()->toArray('id');
                    $placementTypeIdSql = \Tk\Db\Mapper::makeMultipleQuery($placementTypeIds, 'a1.placement_type_id');
                    $statusSql = \Tk\Db\Mapper::makeMultipleQuery($assessment->getPlacementStatus(), 'a1.status');
                    $maxReminder = 5; // initial + $assessment_repeat_cycles

                    $sql = sprintf("
SELECT *
FROM (
SELECT DISTINCT a1.*
    FROM placement a1
    LEFT JOIN ca_entry ce ON (a1.id = ce.placement_id AND ce.assessment_id = {$assessment->getId()})
WHERE
    ($placementTypeIdSql)
    AND a1.subject_id = {$subject->getId()}
    AND ($statusSql)
    AND ce.id IS NULL
    AND DATE(a1.date_end) > '2020-01-01' -- So older placements are not included
    AND DATE(DATE_ADD(a1.date_end, INTERVAL 7 DAY)) <= DATE(NOW())
ORDER BY a1.date_end DESC
) a, subject b, ca_assessment c, (
  SELECT assessment_id, placement_id, SUM(date) as 'reminder_count'
    FROM ca_reminder
    GROUP BY assessment_id, placement_id
) d

WHERE a.subject_id = b.id AND b.course_id = c.course_id AND c.id = {$assessment->getId()}
    AND a.id = d.placement_id AND c.id = d.assessment_id
ORDER BY a.date_end DESC
");



                    $sql = sprintf("SELECT *
FROM (
        SELECT DISTINCT a1.*
        FROM placement a1
        LEFT JOIN ca_entry ce ON (a1.id = ce.placement_id AND ce.assessment_id = %s)
        WHERE
    --      (a1.placement_type_id = '9')
    --      AND a1.subject_id = 57
    --      AND (a1.status = 'assessing' OR a1.status = 'completed' OR a1.status = 'evaluating' OR a1.status = 'failed')
          ce.id IS NULL
          AND DATE(a1.date_end) > '2020-01-01' -- So older placements are not included
          AND DATE(DATE_ADD(a1.date_end, INTERVAL 7 DAY)) <= DATE(NOW())
        ORDER BY a1.date_end DESC
    ) a,
    (
         SELECT a2.id as 'placement_id', %s as 'assessment_id', IFNULL(COUNT(b2.date), 0) as 'reminder_count', MAX(b2.date) as 'last_sent'
         FROM placement a2 LEFT JOIN ca_reminder b2 ON (a2.id = b2.placement_id AND b2.assessment_id = 3)
         WHERE a2.placement_type_id = '9' AND a2.subject_id = 57
           AND (a2.status = 'assessing' OR a2.status = 'completed' OR a2.status = 'evaluating' OR a2.status = 'failed')
         GROUP BY a2.id
    ) b
    , subject e, ca_assessment f
WHERE a.id = b.placement_id
      AND b.reminder_count < 5
      AND a.subject_id = e.id AND e.course_id = f.course_id AND f.id = 3
ORDER BY a.date_end DESC
", $assessment->getId(), $assessment->getId(),  );
                    vd($sql);
                    $res = $this->getConfig()->getDb()->query($sql);
                    $console->writeComment('        Rows: ' . $res->rowCount());

                }
            }

        }







// Sample SQL query??
// ---------------------

//    ;


// Placement reminder code for example only...
// -----------------------------------------------------------
//
//        $this->write(' - Sending Placement Reminders.');
//        $filter = array(
//            'historic' => false,
//            'institutionId' => $institution->getId(),
//            'reminder' => true,
//            'status' => array(\App\Db\Placement::STATUS_APPROVED)
//        );
//
//        $list = \App\Db\PlacementMap::create()->findFiltered($filter);
//        foreach($list as $i => $placement) {
//            $status = \Uni\Db\Status::create($placement, 'Reminder');
//            $status->setEvent('message.app.placement.company.reminder');
//
//            $e = new \Uni\Event\StatusEvent($status);
//            \App\Config::getInstance()->getEventDispatcher()->dispatch(\Uni\StatusEvents::STATUS_CHANGE, $e);
//            \App\Config::getInstance()->getEventDispatcher()->dispatch(\Uni\StatusEvents::STATUS_SEND_MESSAGES, $e);
//            // Mark placement as reminder sent
//            $placement->setReminder(\Tk\Date::create());
//            $placement->save();
//            $this->write('   + [' .$placement->getId() . '] ' . $placement->getTitle(true));
//        }
//        $this->write('   Processed: ' . $list->count());



    }



    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            \App\AppEvents::CONSOLE_CRON => array('onCron', 0)
        );
    }
    
}