<?php
namespace Ca\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Competency::create();
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
class Competency extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        
        $this->appendField(new Field\Input('uid'));
        $this->appendField(new Field\Select('courseId', array()))->prependOption('-- Select --', '');
        $this->appendField(new Field\Input('name'));
        $this->appendField(new Field\Textarea('description'));

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
        $this->load(\Ca\Db\CompetencyMap::create()->unmapForm($this->getCompetency()));
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
        \Ca\Db\CompetencyMap::create()->mapForm($form->getValues(), $this->getCompetency());

        // Do Custom Validations

        $form->addFieldErrors($this->getCompetency()->validate());
        if ($form->hasErrors()) {
            return;
        }
        
        $isNew = (bool)$this->getCompetency()->getId();
        $this->getCompetency()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('competencyId', $this->getCompetency()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\Ca\Db\Competency
     */
    public function getCompetency()
    {
        return $this->getModel();
    }

    /**
     * @param \Ca\Db\Competency $competency
     * @return $this
     */
    public function setCompetency($competency)
    {
        return $this->setModel($competency);
    }
    
}