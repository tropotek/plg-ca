<?php
namespace Ca\Listener;

use Symfony\Component\HttpKernel\KernelEvents;
use Tk\Event\Subscriber;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 * @todo; we still need to implement this
 */
class PlacementReportHandler implements Subscriber
{


    /**
     * @var \App\Db\Subject|\Uni\Db\SubjectIface
     */
    private $subject = null;

    /**
     * @var null|\App\Controller\Placement\ReportEdit|\Ca\Controller\Entry\Edit
     */
    protected $controller = null;


    /**
     * PlacementManagerHandler constructor.
     * @param \App\Db\Subject|\Uni\Db\SubjectIface $subject
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $event
     */
    public function onControllerInit($event)
    {
        /** @var \App\Controller\Placement\ReportEdit|\Ca\Controller\Entry\Edit $controller */
        $controller = \Tk\Event\Event::findControllerObject($event);
        if ($controller instanceof \App\Controller\Placement\ReportEdit || $controller instanceof \Ca\Controller\Entry\Edit) {
            $this->controller = $controller;
        }
    }

    /**
     * @param \Tk\Event\Event $event
     */
    public function onPageInit($event)
    {
        if ($this->controller) {

            //TODO: Do we need this link in the reportEdit page
            return;
            //TODO: -------------------------------------------

            if (!$this->getConfig()->getUser()->isStudent() && $this->controller instanceof \App\Controller\Placement\ReportEdit) {
                $placement = $this->controller->getPlacement();
                $assessmentList = \Ca\Db\AssessmentMap::create()->findFiltered(array(
                    'subjectId' => $this->subject->getId(),
                    'assessorGroup' => \Ca\Db\Assessment::ASSESSOR_GROUP_STUDENT
                ));

                /** @var \Ca\Db\Assessment $assessment */
                foreach ($assessmentList as $assessment) {
                    if (!$assessment->isAvailable($placement)) {
                        continue;
                    }
                    /** @var \Ca\Db\Entry $entry */
                    $entry = \Ca\Db\EntryMap::create()->findFiltered(array(
                            'assessmentId' => $assessment->getId(),
                            'placementId' => $placement->getId())
                    )->current();
                    $url = \Uni\Uri::createSubjectUrl('/ca/entryEdit.html')
                        ->set('placementId', $placement->getId())
                        ->set('assessmentId', $assessment->getId());
                    /** @var \Tk\Ui\Link $btn */
                    $btn = $this->controller->getActionPanel()->append(\Tk\Ui\Link::createBtn($assessment->getName(), $url, $assessment->getIcon()));
                    $btn->setAttr('title', 'Edit ' . $assessment->getName());
                    if (!$entry) {
                        $btn->removeCss('btn-default')->addCss('btn-success')->setAttr('title', 'Create ' . $assessment->getName());
                    }

                }
            }

        }
    }

    /**
     * Check the user has access to this controller
     *
     * @param \Tk\Event\TableEvent $event
     * @throws \Exception
     */
    public function onSubmit(\Tk\Event\FormEvent $event)
    {
        if (!$this->controller) return;
        $submitEvent = $event->getForm()->getTriggeredEvent();
        $submitEvent->appendCallback(array($this, 'onSave'));

    }

    /**
     * @param \Tk\Form $form
     * @param \Tk\Form\Event\Iface $event
     * @throws \Exception
     */
    public function onSave($form, $event)
    {

        if ($form->hasErrors()) {
            return;
        }

        if (!$this->getConfig()->getUser()->isStudent()) return;

        $placement = $this->controller->getPlacement();
        $assessmentList = \Ca\Db\AssessmentMap::create()->findFiltered(array(
            'subjectId' => $this->subject->getId(),
            'assessorGroup' => \Ca\Db\Assessment::ASSESSOR_GROUP_STUDENT
        ));

        if ($this->controller->getBackUrl() &&
            ($this->controller->getBackUrl()->basename() != 'entryEdit.html' ||
            $this->controller->getBackUrl()->basename() != 'reportEdit.html')) return;

        /** @var \Ca\Db\Assessment $assessment */
        foreach ($assessmentList as $assessment) {
                if (!$assessment->isAvailable($placement)) {
                    continue;
                }
                /** @var \Ca\Db\Entry $entry */
                $entry = \Ca\Db\EntryMap::create()->findFiltered(array(
                    'assessmentId' => $assessment->getId(),
                    'placementId' => $placement->getId())
                )->current();
                if ($entry && $entry->getStatus() != \Ca\Db\Entry::STATUS_PENDING && $entry->getStatus() != \Ca\Db\Entry::STATUS_AMEND) continue;
                $url = \Uni\Uri::createSubjectUrl('/ca/entryEdit.html')
                    ->set('placementId', $placement->getId())
                    ->set('assessmentId', $assessment->getId());
                \Tk\Alert::addInfo('Please submit the following ' . $assessment->getName() . ' Form');
                $event->setRedirect($url);
                return;
        }

//        $url = \Uni\Uri::createSubjectUrl('/index.html');
//        $event->setRedirect($url);
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
            KernelEvents::CONTROLLER => array('onControllerInit', 0),
            \Tk\PageEvents::CONTROLLER_INIT => array('onPageInit', 0),
            \Tk\Form\FormEvents::FORM_SUBMIT => array(array('onSubmit', 0))
        );
    }

    /**
     * @return \App\Config|\Tk\Config
     */
    protected function getConfig()
    {
        return \App\Config::getInstance();
    }

}