<?php
namespace Ca\Listener;

use Ca\Db\AssessmentMap;
use Tk\ConfigTrait;
use Tk\Event\Subscriber;

/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StaffSideMenuHandler implements Subscriber
{
    use ConfigTrait;

    /**
     * Check the user has access to this controller
     *
     * @param \Tk\Event\Event $event
     * @throws \Tk\Db\Exception
     * @throws \Tk\Exception
     */
    public function onControllerInit(\Tk\Event\Event $event)
    {
        /** @var \App\Controller\Iface $controller */
        $controller = $event->get('controller');

        if ($controller->getConfig()->getSubject() && $this->getAuthUser() && $controller->getAuthUser()->isStaff()) {
            /** @var \App\Page $page */
            $page = $controller->getPage();
            /** @var \App\Ui\Sidebar\StaffMenu $sideBar */
            $sideBar = $page->getSidebar();
            if ($sideBar instanceof \App\Ui\Sidebar\StaffMenu) {
                $assessments = AssessmentMap::create()->findFiltered(['subjectId' => $this->getConfig()->getSubjectId()]);
                $name = 'Assessment Reports';
                $sideBar->setDropdownIcon($name, 'fa-stethoscope');
                foreach ($assessments as $assessment) {
                    $sideBar->addDropdownItem($name, \Tk\Ui\Link::create($assessment->getName(),
                        \Uni\Uri::createSubjectUrl('/ca/entryManager.html')->set('assessmentId', $assessment->getId()),
                        $assessment->getIcon()));
                }

                // TODO: add average reporting???

            }
        }
    }


    /**
     * Check the user has access to this controller
     *
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