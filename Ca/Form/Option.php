<?php
namespace Ca\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Option::create();
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
class Option extends \Uni\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {

        $this->appendField(new Field\Input('name'));
        $this->appendField(new Field\Input('description'));
        $this->appendField(new Field\Input('value'));

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
        $this->load(\Ca\Db\OptionMap::create()->unmapForm($this->getOption()));
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
        \Ca\Db\OptionMap::create()->mapForm($form->getValues(), $this->getOption());

        // Do Custom Validations

        $form->addFieldErrors($this->getOption()->validate());
        if ($form->hasErrors()) {
            return;
        }
        
        $isNew = (bool)$this->getOption()->getId();
        $this->getOption()->save();

        // Do Custom data saving
        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        //if ($form->getTriggeredEvent()->getName() == 'save') {
        //    $event->setRedirect(\Tk\Uri::create()->set('scaleId', $this->getOption()->getScaleId()));
        //}
    }

    /**
     * @return \Tk\Db\ModelInterface|\Ca\Db\Option
     */
    public function getOption()
    {
        return $this->getModel();
    }

    /**
     * @param \Ca\Db\Option $option
     * @return $this
     */
    public function setOption($option)
    {
        return $this->setModel($option);
    }
    
}