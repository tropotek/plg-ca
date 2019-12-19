<?php
namespace Ca\Listener;

use Symfony\Component\HttpKernel\KernelEvents;
use Tk\ConfigTrait;
use Tk\Event\Subscriber;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 * @todo; we still need to implement this
 */
class PlacementReportHandler implements Subscriber
{
    use ConfigTrait;

    const SID = 'submitted';

    /**
     * @var \App\Db\Subject|\Uni\Db\SubjectIface
     */
    private $subject = null;

    /**
     * @var null|\App\Controller\Placement\ReportEdit|\Ca\Controller\Entry\Edit
     */
    protected $controller = null;

    /**
     * a list of recently submitted assessments
     *
     * @var array|mixed
     */
    private $submitted = array();

    /**
     * PlacementManagerHandler constructor.
     * @param \App\Db\Subject|\Uni\Db\SubjectIface $subject
     */
    public function __construct($subject)
    {
        $this->subject = $subject;
        if (!$this->getSession()->get(self::SID))
            $this->getSession()->set(self::SID, $this->submitted);
        $this->submitted = $this->getSession()->get(self::SID);
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
            if ($controller instanceof \App\Controller\Placement\ReportEdit) {
                $this->getSession()->remove(self::SID);
            }
        }
    }

    /**
     * @param \Tk\Event\Event $event
     */
    public function onPageInit($event)
    {
        if ($this->controller) {



        }
    }

    /**
     * Check the user has access to this controller
     *
     * @param \Tk\Event\TableEvent $event
     * @throws \Exception
     */
    public function onFormLoad(\Tk\Event\FormEvent $event)
    {
        if (!$this->controller) return;

        $assessmentList = \Ca\Db\AssessmentMap::create()->findFiltered(array(
            'courseId' => $this->subject->getCourseId(),
            'assessorGroup' => \Ca\Db\Assessment::ASSESSOR_GROUP_STUDENT
        ));

        $submitEvent = $event->getForm()->getField('update');
        if ($event->getForm()->getField('save'))
            $event->getForm()->removeField('save');
        if ($assessmentList->count()) {
            $submitEvent->setLabel('Next');
            $submitEvent->addCss('pull-right');
            $submitEvent->setIconRight('fa fa-arrow-right')->setIcon('');
            $submitEvent->appendCallback(array($this, 'onSave'));
        }
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
            'courseId' => $this->subject->getCourseId(),
            'assessorGroup' => \Ca\Db\Assessment::ASSESSOR_GROUP_STUDENT
        ));

        /** @var \Ca\Db\Assessment $assessment */
        foreach ($assessmentList as $assessment) {
                if (!$assessment->isAvailable($placement) || $this->isSubmitted($assessment)) {
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

                $this->submitted[$assessment->getId()] = $assessment->getId();
                $this->getSession()->set(self::SID, $this->submitted);
                return;
        }
        $event->setRedirect(\Uni\Uri::createSubjectUrl('/index.html'));
        $this->getSession()->remove(self::SID);
    }

    /**
     * @param \Ca\Db\Assessment $assessment
     */
    public function isSubmitted($assessment)
    {
        return in_array($assessment->getId(), $this->submitted);
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
            \Tk\Form\FormEvents::FORM_LOAD => array(array('onFormLoad', 0))
        );
    }

}