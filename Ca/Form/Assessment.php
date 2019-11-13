<?php
namespace Ca\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Assessment::create();
 *   $form->setModel($obj);
 *   $formTemplate = $form->getRenderer()->show();
 *   $template->appendTemplate('form', $formTemplate);
 * </code>
 * 
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Assessment extends \Uni\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        $layout = $this->getRenderer()->getLayout();
        $layout->removeRow('name', 'col-md-6');
        $layout->removeRow('assessorGroup', 'col-md-6');
        $layout->removeRow('includeZero', 'col-md-6');
        $layout->removeRow('icon', 'col-md-6');
        
        //$this->appendField(new Field\Input('uid'));
        //$this->appendField(new Field\Select('courseId', array()))->prependOption('-- Select --', '');
        $this->appendField(new Field\Input('name'));
        $this->appendField(new Field\Select('assessorGroup', \Ca\Db\Assessment::getAssessorGroupList($this->getAssessment()->getAssessorGroup())))
            ->setNotes('Who is the user group to submit the assessment evaluations.');

        $list = array('tk tk-clear', 'tk tk-goals', 'fa fa-eye', 'fa fa-user-circle-o', 'fa fa-bell', 'fa fa-certificate', 'fa fa-tv', 'fa fa-drivers-license',
            'fa fa-leaf', 'fa fa-trophy', 'fa fa-ambulance', 'fa fa-rebel', 'fa fa-empire', 'fa fa-font-awesome', 'fa fa-heartbeat',
            'fa fa-medkit', 'fa fa-user-md', 'fa fa-user-secret', 'fa fa-heart');
        $this->appendField(new Field\Select('icon', Field\Select::arrayToSelectList($list, false)))
            ->addCss('iconpicker')->setNotes('Select an identifying icon for this assessment');

        $this->appendField(new Field\Checkbox('includeZero'))->setCheckboxLabel('When calculating the score should 0 value results be included.');

        // TODO: Hide this when assessor group is 'student'
        $this->appendField(new Field\Select('placementStatus[]', \App\Db\Placement::getStatusList()))->addCss('tk-dual-select')
            ->setNotes('Select the placement status values when assessments become available and can be submitted.');

        $list = \App\Db\PlacementTypeMap::create()->findFiltered(array('profileId' => $this->getAssessment()->getCourseId()));
        $ptiField = $this->appendField(new Field\Select('placementTypeId[]', $list))
            ->addCss('tk-dual-select')->setAttr('data-title', 'Placement Types')
            ->setNotes('Enable this assessment for the selected placement types.');

        $list = \Ca\Db\AssessmentMap::create()->findPlacementTypes($this->getAssessment()->getId());
        $ptiField->setValue($list);


        $this->appendField(new Field\Textarea('description'))->setLabel('Instructions');

        $this->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->appendField(new Event\Link('cancel', $this->getBackUrl()));

    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function execute($request = null)
    {
        $this->load(\Ca\Db\AssessmentMap::create()->unmapForm($this->getAssessment()));
        parent::execute($request);
    }

    /**
     * @param Form $form
     * @param Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        // Load the object with form data
        \Ca\Db\AssessmentMap::create()->mapForm($form->getValues(), $this->getAssessment());

        // Do Custom Validations
        $placemenTypeIds = $form->getFieldValue('placementTypeId');
        if(!$this->getAssessment()->isSelfAssessment() && (!is_array($placemenTypeIds) || !count($placemenTypeIds)) ) {
            $form->addFieldError('placementTypeId', 'Please select at least one placement type for this collection to be enabled for.');
        }

        $form->addFieldErrors($this->getAssessment()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getAssessment()->getId();
        $this->getAssessment()->save();

        \Ca\Db\AssessmentMap::create()->removePlacementType($this->getAssessment()->getId());
        if (is_array($placemenTypeIds) && count($placemenTypeIds)) {
            foreach ($placemenTypeIds as $placementTypeId) {
                \Ca\Db\AssessmentMap::create()->addPlacementType($this->getAssessment()->getId(), $placementTypeId);
            }
        }

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('assessmentId', $this->getAssessment()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\Ca\Db\Assessment
     */
    public function getAssessment()
    {
        return $this->getModel();
    }

    /**
     * @param \Ca\Db\Assessment $assessment
     * @return $this
     */
    public function setAssessment($assessment)
    {
        return $this->setModel($assessment);
    }
    
}