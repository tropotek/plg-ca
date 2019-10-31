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
class Assessment extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        
        //$this->appendField(new Field\Input('uid'));
        //$this->appendField(new Field\Select('courseId', array()))->prependOption('-- Select --', '');
        $this->appendField(new Field\Input('name'));
        $this->appendField(new Field\Input('icon'));
        $this->appendField(new Field\Input('statusAvailable'));
        $this->appendField(new Field\Input('assessorGroup'));
        $this->appendField(new Field\Checkbox('multi'));
        $this->appendField(new Field\Checkbox('includeZero'));
        $this->appendField(new Field\Input('publishResult'));
        $this->appendField(new Field\Textarea('description'));
        //$this->appendField(new Field\Textarea('notes'));

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

        $form->addFieldErrors($this->getAssessment()->validate());
        if ($form->hasErrors()) {
            return;
        }
        
        $isNew = (bool)$this->getAssessment()->getId();
        $this->getAssessment()->save();

        // Do Custom data saving

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