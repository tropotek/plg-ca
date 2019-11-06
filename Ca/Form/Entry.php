<?php
namespace Ca\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Entry::create();
 *   $form->setModel($obj);
 *   $formTemplate = $form->getRenderer()->show();
 *   $template->appendTemplate('form', $formTemplate);
 * </code>
 * 
 * @author Mick Mifsud
 * @created 2019-11-06
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Entry extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {

        // $this->appendField(new Field\Input('title'));        // HTML from auto gen title
        $this->appendField(new Field\Input('assessorName'))->setRequired();
        $this->appendField(new Field\Input('assessorEmail'));
        $this->appendField(new Field\Input('absent'));
        //$this->appendField(new Field\Input('average'));       // HTML
        $this->appendField(new Field\Select('status', \Ca\Db\Entry::getStatusList($this->getEntry()->getStatus())));
        $this->appendField(new Field\Textarea('notes'));

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
        $this->load(\Ca\Db\EntryMap::create()->unmapForm($this->getEntry()));
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
        \Ca\Db\EntryMap::create()->mapForm($form->getValues(), $this->getEntry());

        // Do Custom Validations

        $form->addFieldErrors($this->getEntry()->validate());
        if ($form->hasErrors()) {
            return;
        }
        
        $isNew = (bool)$this->getEntry()->getId();
        $this->getEntry()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('entryId', $this->getEntry()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\Ca\Db\Entry
     */
    public function getEntry()
    {
        return $this->getModel();
    }

    /**
     * @param \Ca\Db\Entry $entry
     * @return $this
     */
    public function setEntry($entry)
    {
        return $this->setModel($entry);
    }
    
}