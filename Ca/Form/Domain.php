<?php
namespace Ca\Form;

use Tk\Form;
use Tk\Form\Event;
use Tk\Form\Field;

/**
 * Example:
 * <code>
 *   $form = new Domain::create();
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
class Domain extends \Uni\FormIface
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
        $this->appendField(new Field\Input('label'));
        $this->appendField(new Field\Input('orderBy'));

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
        $this->load(\Ca\Db\DomainMap::create()->unmapForm($this->getDomain()));
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
        \Ca\Db\DomainMap::create()->mapForm($form->getValues(), $this->getDomain());

        // Do Custom Validations

        $form->addFieldErrors($this->getDomain()->validate());
        if ($form->hasErrors()) {
            return;
        }
        
        $isNew = (bool)$this->getDomain()->getId();
        $this->getDomain()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('domainId', $this->getDomain()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\Ca\Db\Domain
     */
    public function getDomain()
    {
        return $this->getModel();
    }

    /**
     * @param \Ca\Db\Domain $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        return $this->setModel($domain);
    }
    
}