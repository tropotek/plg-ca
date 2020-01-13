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
class PlacementManagerHandler implements Subscriber
{


    /**
     * @var \App\Db\Subject|\Uni\Db\SubjectIface
     */
    private $subject = null;

    /**
     * @var null|\App\Controller\Placement\Manager
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
        /** @var \App\Controller\Placement\Edit $controller */
        $controller = \Tk\Event\Event::findControllerObject($event);
        if ($controller instanceof \App\Controller\Placement\Manager) {
            $config = \Uni\Config::getInstance();
            $this->controller = $controller;
        }
    }

    /**
     * Check the user has access to this controller
     *
     * @param \Tk\Event\TableEvent $event
     * @throws \Exception
     */
    public function addActions(\Tk\Event\TableEvent $event)
    {
        if ($this->controller) {
            $assessmentList = \Ca\Db\AssessmentMap::create()->findFiltered(array(
                'subjectId' => $this->subject->getId()
            ));

            /** @var \Tk\Table\Cell\ButtonCollection $actionsCell */
            $actionsCell = $event->getTable()->findCell('actions');

            /** @var \Ca\Db\Assessment $assessment */
            foreach ($assessmentList as $assessment) {
                $url = \Uni\Uri::createSubjectUrl('/ca/entryEdit.html')->set('assessmentId', $assessment->getId());

                $cell = $actionsCell->append(\Tk\Table\Ui\ActionButton::createBtn($assessment->getName(), $url, $assessment->getIcon()))
                    ->setOnShow(function ($cell, $obj, $btn) use ($assessment) {
                        /* @var $obj \App\Db\Placement */
                        /* @var $btn \Tk\Table\Cell\ActionButton */
                        $placementAssessment = \Ca\Db\AssessmentMap::create()->findFiltered(
                            array('subjectId' => $obj->getSubjectId(), 'uid' => $assessment->getUid())
                        )->current();
                        if (!$placementAssessment) $placementAssessment = $assessment;

                        $btn->setUrl(\Uni\Uri::createSubjectUrl('/ca/entryEdit.html', $obj->getSubject())
                            ->set('assessmentId', $placementAssessment->getId()));
                        $btn->getUrl()->set('placementId', $obj->getId());
                        if (!$placementAssessment->isAvailable($obj)) {
                            $btn->setVisible(false);
                            return;
                        }

                        $entry = \Ca\Db\EntryMap::create()->findFiltered(array(
                            'assessmentId' => $placementAssessment->getId(),
                            'placementId' => $obj->getId())
                        )->current();

                        if ($entry) {
                            $btn->addCss('btn-default');
                            $btn->setText('Edit ' . $placementAssessment->getName());
                        } else {
                            $btn->addCss('btn-success');
                            $btn->setText('Create ' . $placementAssessment->getName());
                        }
                    });
                if ($assessment->isSelfAssessment()) {
                    $cell->setGroup('feedback');
                } else {
                    $cell->setGroup('ca');
                }
                // Add Entry Columns for csv exports
                $aName = 'assessment_'.$assessment->getId();
                $aLabel = $assessment->getName();
                if ($assessment->getPlacementTypes()->count() == 1)
                    $aLabel .= ' ['.$assessment->getPlacementTypeName().']';
                $cell = $event->getTable()->appendCell(new \Tk\Table\Cell\Text($aName), 'placementReportId')->setLabel($aLabel)->setOrderProperty('')
                    ->setOnPropertyValue(function ($cell, $obj, $value) use ($assessment) {
                        /** @var $obj \App\Db\Placement */
                        $placementAssessment = \Ca\Db\AssessmentMap::create()->findFiltered(
                            array('subjectId' => $obj->getSubjectId(), 'uid' => $assessment->getUid())
                        )->current();
                        if (!$placementAssessment) $placementAssessment = $assessment;

                        if ($placementAssessment->isAvailable($obj)) {
                            $entry = \Ca\Db\EntryMap::create()->findFiltered(array(
                                'assessmentId' => $placementAssessment->getId(),
                                'placementId' => $obj->getId())
                            )->current();
                            if ($entry) {
                                return 'Yes';
                            }
                        }
                        return 'No';
                    });
                /** @var \Tk\Table\Action\ColumnSelect $columns */
                $columns = $event->getTable()->findAction('columns');
                $columns->addUnselected($aName);
            }
        }
    }

    /**
     * Check the user has access to this controller
     *
     * @param \Tk\Event\TableEvent $event
     * @throws \Exception
     */
    public function addEntryCell(\Tk\Event\TableEvent $event)
    {
        if ($this->controller) {
            $assessmentList = \Ca\Db\AssessmentMap::create()->findFiltered(array(
                'subjectId' => $this->subject->getId(),
                'assessorGroup' => \Ca\Db\Assessment::ASSESSOR_GROUP_COMPANY
            ));
            $table = $event->getTable();
            $table->appendCell(\Tk\Table\Cell\Link::create('assessmentLinks'))
                ->setLabel('Assessment Links')
                ->setOnPropertyValue(function ($cell, $obj, $value) use ($assessmentList) {
                    /** @var \App\Db\Placement $obj */
                    $value = '';
                    /** @var \Ca\Db\Assessment $assessment */
                    foreach ($assessmentList as $assessment) {
                        if ($assessment->isAvailable($obj)) {
                            $value .= $assessment->getPublicUrl($obj->getHash())->toString();
                        }
                    }
                    return $value;
                })
                ->setOnCellHtml(function ($cell, $obj, $html) use ($assessmentList) {
                    /** @var \Tk\Table\Cell\Link $cell */
                    /** @var \App\Db\Placement $obj */
                    $html = '';
                    /** @var \Ca\Db\Assessment $assessment */
                    foreach ($assessmentList as $assessment) {
                        if ($assessment->isAvailable($obj)) {
                            $html .= sprintf('<a href="%s" class="btn btn-xs btn-default" title="%s" target="_blank"><i class="%s"></i></a>',
                                htmlentities($assessment->getPublicUrl($obj->getHash())), $assessment->getName(), $assessment->getIcon());
                        }
                    }
                    return '<div class="btn-toolbar" role="toolbar">'.$html.'</div>';
                });
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
            //\Tk\PageEvents::CONTROLLER_INIT => array('addActions', 0),
            //\Tk\PageEvents::CONTROLLER_INIT => array(array('onControllerInit', 0), array('addEntryCell', 0)),
            \Tk\Table\TableEvents::TABLE_INIT => array(array('addActions', 0), array('addEntryCell', 0))
        );
    }

}