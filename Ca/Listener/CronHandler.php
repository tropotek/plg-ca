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

        $console->write(' - Sending Assessment Reminders');

        // Placement Evaluating reminder after 7 days, then send every 28 days, for max 4 months.

//        $console->writeBlue('    - TODO: Send initial assessment reminder');
//        $console->writeBlue('    - TODO: Send any outstanding reminders less than max number of times.');
//        $console->writeBlue('    - TODO: For any reminders on their last send, also email the subject coordinator informing them of the issue.');
//        $console->write(' ');

        // Get enabled courses
        $plugin = \Ca\Plugin::getInstance();
        $courseList = $plugin->getPluginFactory()->getPluginZoneIdList($plugin->getName(), \App\Plugin\Iface::ZONE_COURSE);

        foreach ($courseList as $courseData) {
            /** @var \Uni\Db\CourseIface $course */
            $course = $this->getConfig()->getCourseMapper()->find($courseData->zone_id);
            if (!$course) continue;
            $assessmentList = \Ca\Db\AssessmentMap::create()->findFiltered(array('courseId' => $courseData->zone_id, 'enableReminder' => true));
            if (!$assessmentList->countAll()) continue;

            $console->write('    Course: ' . $course->getName());
            $subjectList = $this->getConfig()->getSubjectMapper()->findFiltered(array('courseId' => $course->getId(), 'active' => true));
            foreach ($subjectList as $subject) {
                $console->write('      Subject: ' . $subject->getName());
                foreach ($assessmentList as $assessment) {
                    if (!$assessment->isEnableReminder() || !$assessment->isActive($subject->getId())) continue;
                    $date =  new \DateTime('today -'.$assessment->getReminderInitialDays().' days');
                    $console->writeComment('       Assess: ' . $assessment->getName() . ' - ' . $assessment->getPlacementTypeName() . ' [' . $assessment->getId() . ']');
                    $console->writeComment('        Date From: ' . $date->format(\Tk\Date::FORMAT_SHORT_DATE));

                    $placementTypeIds = $assessment->getPlacementTypes()->toArray('id');
                    $placementTypeIdSql = \Tk\Db\Mapper::makeMultipleQuery($placementTypeIds, 'a2.placement_type_id');
                    $statusSql = \Tk\Db\Mapper::makeMultipleQuery($assessment->getPlacementStatus(), 'a2.status');
                    $maxReminder = $assessment->getReminderRepeatCycles()+1;

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
          AND DATE(a1.date_start) > '2020-01-01' -- So older placements are not included
          AND DATE(DATE_ADD(a1.date_end, INTERVAL 7 DAY)) <= DATE(NOW())
        ORDER BY a1.date_end DESC
    ) a,
    (
         SELECT a2.id as 'placement_id', %s as 'assessment_id', IFNULL(COUNT(b2.date), 0) as 'reminder_count', MAX(b2.date) as 'last_sent'
         FROM placement a2 LEFT JOIN ca_reminder b2 ON (a2.id = b2.placement_id AND b2.assessment_id = %s)
         WHERE 
           (%s) AND 
           a2.subject_id = %s AND
           (%s)
         GROUP BY a2.id
    ) b
--    , subject e, ca_assessment f
WHERE a.id = b.placement_id
      AND b.reminder_count <= %s
--       AND a.subject_id = e.id AND e.course_id = f.course_id AND f.id = %s
ORDER BY a.date_end DESC
", $assessment->getId(), $assessment->getId(), $assessment->getId(), $placementTypeIdSql, $subject->getId(), $statusSql, $maxReminder, $assessment->getId());
                    $res = $this->getConfig()->getDb()->query($sql);
                    $console->writeComment('        Reminders: ' . $res->rowCount());
                    $sentCnt = 0;;
                    foreach($res as $i => $row) {
                        /** @var \App\Db\Placement $placement */
                        $placement = \App\Db\PlacementMap::create()->find($row->id);
                        if (!$placement) continue;
                        $reminderCount = $row->reminder_count;
                        $lastSent = null;
                        if ($row->last_sent)
                            $lastSent = \Tk\Date::floor(\Tk\Date::create($row->last_sent));
                        $days = $assessment->getReminderInitialDays() + ($assessment->getReminderRepeatDays() * $reminderCount);
                        $nextReminderDate = \Tk\Date::floor($placement->getDateEnd()->add(new \DateInterval('P'.$days.'D')));
                        $now = \Tk\Date::floor();

                        //vd($reminderCount, $now, $nextReminderDate);
                        // compare dates, last date and number of reminders sent to see what should be sent and what should not.
                        if (!$lastSent || $now >= $nextReminderDate ) {
                            $entry = \Ca\Db\Entry::create($placement, $assessment);      // Do not save() this status and entry..
                            $entry->setStatus('reminder');

                            $status = \Uni\Db\Status::create($entry, 'Assessment Reminder');
                            $status->setEvent('message.ca.entry.reminder');

                            $e = new \Uni\Event\StatusEvent($status);
                            $this->getConfig()->getEventDispatcher()->dispatch(\Uni\StatusEvents::STATUS_CHANGE, $e);
                            $this->getConfig()->getEventDispatcher()->dispatch(\Uni\StatusEvents::STATUS_SEND_MESSAGES, $e);

                            // Mark reminder sent
                            $sentCnt++;
                            $stmt = $this->getDb()->prepare('INSERT INTO ca_reminder (assessment_id, placement_id, date) VALUES (?, ?, NOW())');
                            $stmt->execute(array(
                                $assessment->getId(),
                                $placement->getId()
                            ));
                        }
                    }
                    $console->writeComment('             Sent: ' . $sentCnt);

                }
            }

        }

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