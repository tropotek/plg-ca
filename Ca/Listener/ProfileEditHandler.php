<?php
namespace Ca\Listener;

use Tk\Event\Subscriber;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class ProfileEditHandler implements Subscriber
{

    /**
     * @param \Tk\Event\Event $event
     * @throws \Exception
     */
    public function onControllerInit(\Tk\Event\Event $event)
    {
        /** @var \App\Controller\Profile\Edit $controller */
        $controller = $event->get('controller');
        if ($controller instanceof \App\Controller\Profile\Edit) {
            if ($controller->getUser()->isStaff() && $controller->getCourse()) {
                /** @var \Tk\Ui\Admin\ActionPanel $actionPanel */
                $actionPanel = $controller->getActionPanel();
                $actionPanel->append(\Tk\Ui\Link::createBtn('Assessment Forms',
                    \App\Uri::createHomeUrl('/ca/assessmentManager.html')
                        ->set('profileId', $controller->getCourse()->getId()), 'fa fa-gavel'));
            }
        }
    }

    /**
     * @param \Tk\Event\Event $event
     */
    public function onControllerShow(\Tk\Event\Event $event) { }

    /**
     * @return array The event names to listen to
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            \Tk\PageEvents::CONTROLLER_INIT => array('onControllerInit', 0),
            \Tk\PageEvents::CONTROLLER_SHOW => array('onControllerShow', 0)
        );
    }
    
}