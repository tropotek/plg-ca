<?php
namespace Ca\Listener;

use Tk\Event\Subscriber;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StatusMailHandler implements Subscriber
{

    /**
     * @param \Uni\Event\StatusEvent $event
     * @throws \Exception
     */
    public function onSendAllStatusMessages(\Uni\Event\StatusEvent $event)
    {
        if (!$event->getStatus()->isNotify() || !$event->getStatus()->getCourse()->getCourseProfile()->isNotifications()) return;   // do not send messages

        /** @var \Tk\Mail\CurlyMessage $message */
        foreach ($event->getMessageList() as $message) {

            if ($message->get('placement::id')) {
                /** @var \App\Db\Placement $placement */
                $placement = \App\Db\PlacementMap::create()->find($message->get('placement::id'));
                if ($placement) {
                    $filter = array(
                        'active' => true,
                        'subjectId' => $message->get('placement::subjectId'),
                        'assessorGroup' => \Ca\Db\Assessment::ASSESSOR_GROUP_COMPANY,
                        'placementTypeId' => $placement->placementTypeId
                    );

                    $caLinkHtml = '';
                    $caLinkText = '';
                    $list = \Ca\Db\AssessmentMap::create()->findFiltered($filter);
                    /** @var \Ca\Db\Assessment $assessment */
                    foreach ($list as $assessment) {
                        $key = $assessment->getNameKey();
                        $avail = '';
                        if (!$assessment->isAvailable($placement)) {
                            $avail = ' [Currently Unavailable]';
                        }
                        $url = '';
                        switch($assessment->getAssessorGroup()) {
                            case \Ca\Db\Assessment::ASSESSOR_GROUP_STUDENT:     // Student URL
                                $url = \Uni\Uri::createSubjectUrl('/ca/editEntry.html', $placement->getSubject(), '/student')
                                    ->set('placementId', $placement->getId())
                                    ->set('assessmentId', $assessment->getId())->toString();
                                break;
                            case \Ca\Db\Assessment::ASSESSOR_GROUP_COMPANY:     // Public URL
                                $url = \Uni\Uri::createInstitutionUrl('/assessment.html', $placement->getSubject()->getInstitution())
                                    ->set('h', $placement->getHash())
                                    ->set('assessmentId', $assessment->getId())->toString();
                                break;
//                            case \Ca\Db\Assessment::ASSESSOR_GROUP_STAFF:
//                                break;
                        }
                        $asLinkHtml = sprintf('<a href="%s" title="%s">%s</a>', htmlentities($url),
                            htmlentities($assessment->getName()) . $avail, htmlentities($assessment->getName()) . $avail);
                        $asLinkText = sprintf('%s: %s', htmlentities($assessment->getName()) . $avail, htmlentities($url));
                        $message->set($key.'::linkHtml', $asLinkHtml);
                        $message->set($key.'::linkText', $asLinkText);
//                        $message->set($key.'::id', $assessment->getId());
                        $message->set($key.'::name', $assessment->getName());
//                        $message->set($key.'::placementStatus', $assessment->getPlacementStatus());
//                        $message->set($key.'::description', $assessment->getDescription());
                        $caLinkHtml .= sprintf('<a href="%s" title="%s">%s</a> | ', htmlentities($url),
                            htmlentities($assessment->getName()) . $avail, htmlentities($assessment->getName()) . $avail);
                        $caLinkText .= sprintf('%s: %s | ', htmlentities($assessment->getName()) . $avail, htmlentities($url));
                    }

                    $message->set('ca::linkHtml', rtrim($caLinkHtml, ' | '));
                    $message->set('ca::linkText', rtrim($caLinkText, ' | '));
                }
            }
        }
    }

    /**
     * @param \Tk\Event\Event $event
     * @throws \Exception
     */
    public function onTagList(\Tk\Event\Event $event)
    {
        $course = $event->get('course');
        $list = $event->get('list');

        $list['{assessment::id}'] = 1;
        $list['{assessment::name}'] = 'Assessment Name';
        $list['{assessment::description}'] = 'HTML discription text';

        $list['{entry::id}'] = 1;
        $list['{entry::title}'] = 'Entry Title';
        $list['{entry::assessor}'] = 'Assessor Name';
        $list['{entry::status}'] = 'approved';
        $list['{entry::notes}'] = 'Notes Text';
        $list['{ca::linkHtml}'] = 'All available assessment HTML links';
        $list['{ca::linkText}'] = 'All available assessment Text links';

        $aList = \Ca\Db\AssessmentMap::create()->findFiltered(array('courseId' => $course->getId()));
        foreach ($aList as $assessment) {
            $key = $assessment->getNameKey();
            $tag = sprintf('{%s}{/%s}', $key, $key);
            $list[$tag] = 'Assessment block';
            $list[sprintf('{%s::linkHtml}', $key)] = '<a href="http://www.example.com/form.html" title="Assessment">Assessment</a>';
            $list[sprintf('{%s::linkText}', $key)] = 'Assessment: http://www.example.com/form.html';
//            $list[sprintf('{%s::id}', $key)] = 1;
            $list[sprintf('{%s::name}', $key)] = '"'.$assessment->getName().'"';
//            $list[sprintf('{%s::placementStatus}', $key)] = 'approved, failed, ...';
//            $list[sprintf('{%s::description}', $key)] = 'HTML discription text';
        }

        $event->set('list', $list);
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
            \Uni\StatusEvents::STATUS_SEND_MESSAGES => array('onSendAllStatusMessages', 10),
            \App\AppEvents::MAIL_TEMPLATE_TAG_LIST => array('onTagList', 10)
        );
    }
    
}