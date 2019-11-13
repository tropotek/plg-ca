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
     * @param \App\Event\StatusEvent $event
     * @throws \Exception
     */
    public function onSendAllStatusMessages(\App\Event\StatusEvent $event)
    {
        if (!$event->getStatus()->notify || !$event->getStatus()->getProfile()->notifications) return;   // do not send messages

        /** @var \Tk\Mail\CurlyMessage $message */
        foreach ($event->getMessageList() as $message) {
            $caLinkHtml = '';
            $caLinkText = '';

            if ($message->get('placement::id')) {
                /** @var \App\Db\Placement $placement */
                $placement = \App\Db\PlacementMap::create()->find($message->get('placement::id'));
                if ($placement) {
                    $filter = array(
                        'active' => true,
                        'subjectId' => $message->get('placement::subjectId'),
                        'role' => \Ca\Db\Assessment::ASSESSOR_GROUP_COMPANY,
                        'assessorGroup' => \Ca\Db\Assessment::ASSESSOR_GROUP_COMPANY,
                        //'requirePlacement' => true,
                        'placementTypeId' => $placement->placementTypeId
                    );
                    $list = \Ca\Db\AssessmentMap::create()->findFiltered($filter);
                    /** @var \Ca\Db\Assessment $assessment */
                    foreach ($list as $assessment) {
                        $url = \Uni\Uri::createInstitutionUrl('/assessment.html', $placement->getSubject()->getInstitution())
                            ->set('h', $placement->getHash())
                            ->set('assessmentId', $assessment->getId());
                        $avail = '';
                        if (!$assessment->isAvailable($placement)) {
                            $avail = ' [Currently Unavailable]';
                        }
                        $caLinkHtml .= sprintf('<a href="%s" title="%s">%s</a> | ', htmlentities($url->toString()),
                            htmlentities($assessment->getName()).$avail, htmlentities($assessment->getName()).$avail);
                        $caLinkText .= sprintf('%s: %s | ', htmlentities($assessment->getName()).$avail, htmlentities($url->toString()));
                    }
                }

                // legacy template vars
//                $message->set('skill::linkHtml', rtrim($caLinkHtml, ' | '));
//                $message->set('skill::linkText', rtrim($caLinkText, ' | '));
//                $message->set('placement::goalsUrl', rtrim($caLinkHtml, ' | '));

                // Current
                $message->set('assessment::linkHtml', rtrim($caLinkHtml, ' | '));
                $message->set('assessment::linkText', rtrim($caLinkText, ' | '));
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
            \App\StatusEvents::STATUS_SEND_MESSAGES => array('onSendAllStatusMessages', 10)
        );
    }
    
}