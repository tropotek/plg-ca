<?php
namespace Ca\Listener;

use Bs\DbEvents;
use Symfony\Component\HttpKernel\KernelEvents;
use Tk\ConfigTrait;
use Tk\Event\Event;
use Tk\Event\Subscriber;
use Uni\Db\Subject;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class SubjectEditHandler implements Subscriber
{
    use ConfigTrait;

    /**
     * @var \App\Controller\Subject\Edit
     */
    protected $controller = null;


    /**
     * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
     */
    public function onKernelController($event)
    {
        /** @var \App\Controller\Subject\Edit $controller */
        $controller = \Tk\Event\Event::findControllerObject($event);
        if ($controller instanceof \App\Controller\Subject\Edit) {
            $this->controller = $controller;
        }
    }

    /**
     * Check the user has access to this controller
     *
     * @param Event $event
     * @throws \Exception
     */
    public function onControllerInit(Event $event)
    {
        if ($this->controller) {
            if (!$this->controller->getAuthUser()->isStaff() || !$this->controller->getSubject()->getId()) return;
            /** @var \Tk\Ui\Admin\ActionPanel $actionPanel */
            $actionPanel = $this->controller->getActionPanel();
            $actionPanel->append(\Tk\Ui\Link::createBtn('Active Assessments',
                \Uni\Uri::createSubjectUrl('/ca/activeAssessments.html'), 'fa fa-gavel'));
        }
    }

    /**
     * @var null|Subject
     */
    protected $currSubject = null;

    /**
     * @param \Bs\Event\DbEvent $event
     * @throws \Exception
     */
    public function onModelInsert(\Bs\Event\DbEvent $event)
    {
        if (!$event->getModel() instanceof Subject) {
            return;
        }
        $this->currSubject = $this->getConfig()->getCourse()->getCurrentSubject();

    }


    /**
     * @param \Bs\Event\DbEvent $event
     * @throws \Exception
     */
    public function onModelInsertPost(\Bs\Event\DbEvent $event)
    {
        if (!$event->getModel() instanceof Subject) {
            return;
        }
        if ($this->currSubject) {
            /** @var Subject $subject */
            $subject = $event->getModel();
            // Copy Active CA Assessments
            $filter = array('subjectId' => $this->currSubject->getId());
            $list = \Ca\Db\AssessmentMap::create()->findFiltered($filter);
            foreach ($list as $assessment) {
                if ($assessment->isActive($this->currSubject->getId()))
                    \Ca\Db\AssessmentMap::create()->addSubject($subject->getId(), $assessment->getId());
            }

        }
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array('onKernelController', 0),
            \Tk\PageEvents::CONTROLLER_INIT => array('onControllerInit', 0),
            DbEvents::MODEL_INSERT => 'onModelInsert',
            DbEvents::MODEL_INSERT_POST => 'onModelInsertPost'
        );
    }
    
}