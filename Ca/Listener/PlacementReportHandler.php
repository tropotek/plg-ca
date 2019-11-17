<?php
namespace Ca\Listener;

use Tk\Event\Subscriber;
use Symfony\Component\HttpKernel\KernelEvents;

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

        // Find first self-assessment that needs to be created and redirect with an alert message
        // Keep this loop going until there are none left.
        // TODO: this should give the illusion that we are going next -> next -> submit....

        // TODO: How to find self-assessment entries that have not been created... (See Icon setup)
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
                $url = \App\Uri::createSubjectUrl('/ca/entryEdit.html')
                    ->set('placementId', $placement->getId())
                    ->set('assessmentId', $assessment->getId());
                \Tk\Alert::addInfo('Please submit the following ' . $assessment->getName() . ' Form');
                $event->setRedirect($url);
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
            KernelEvents::CONTROLLER => array('onControllerInit', 0),
            \Tk\Form\FormEvents::FORM_SUBMIT => array(array('onSubmit', 0))
        );
    }

}