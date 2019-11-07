<?php
namespace Ca\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Item::create();
 *   $form->setModel($obj);
 *   $formTemplate = $form->getRenderer()->show();
 *   $template->appendTemplate('form', $formTemplate);
 * </code>
 * 
 * @author Mick Mifsud
 * @created 2019-11-05
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Item extends \Uni\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        $layout = $this->getRenderer()->getLayout();
        $layout->removeRow('name', 'col-md-8');
        $layout->removeRow('gradable', 'col-md-4');
        $layout->removeRow('scaleId', 'col-md-6');
        $layout->removeRow('domainId', 'col-md-6');
        
        //$this->appendField(new Field\Input('uid'));
        //$this->appendField(new Field\Select('assessmentId', array()))->prependOption('-- Select --', '');
        $item = $this->getItem();
        $this->appendField(new Field\Input('name'))->setNotes('(Optional) This value is optional and the first competency will be used if this is blank.')
            ->setOnShow(function ($template, $field) use ($item) {
            /* @var $field Field\Input */
            if (!$item->getName()) {
                /** @var \Ca\Db\Competency $comptetency */
                $list = \Ca\Db\CompetencyMap::create()->findFiltered(array('itemId' => $item->getId()));
                $comptetency = $list->current();
                if ($comptetency && $comptetency->getName())
                    $field->setAttr('placeholder', $comptetency->getName());
            }
        });
        $this->appendField(new Field\Checkbox('gradable'));
        $list = \Ca\Db\ScaleMap::create()->findFiltered(array('institutionId' => $this->getConfig()->getInstitutionId()));
        $this->appendField(new Field\Select('scaleId', $list))->prependOption('-- Select --', '');
        $list = \Ca\Db\DomainMap::create()->findFiltered(array('institutionId' => $this->getConfig()->getInstitutionId()));
        $this->appendField(new Field\Select('domainId', $list))->prependOption('-- Select --', '');
        $this->appendField(new Field\Input('description'));

//        $this->appendField(new Event\Submit('update', array($this, 'doSubmit')));
//        $this->appendField(new Event\Submit('save', array($this, 'doSubmit')));
//        $this->appendField(new Event\Link('cancel', $this->getBackUrl()));

        if ($this->getItem()->getId()) {
            $this->appendField(new Event\Submit('update', array($this, 'doSubmit')));
            $this->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        } else {
            $this->appendField(new Event\Submit('save', array($this, 'doSubmit')))->setLabel('Next');
        }

        $this->appendField(new Event\Link('cancel', $this->getBackUrl()));

    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function execute($request = null)
    {
        $this->load(\Ca\Db\ItemMap::create()->unmapForm($this->getItem()));
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
        \Ca\Db\ItemMap::create()->mapForm($form->getValues(), $this->getItem());

        // Do Custom Validations

        $form->addFieldErrors($this->getItem()->validate());
        if ($form->hasErrors()) {
            return;
        }
        
        $isNew = (bool)$this->getItem()->getId();
        $this->getItem()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('itemId', $this->getItem()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\Ca\Db\Item
     */
    public function getItem()
    {
        return $this->getModel();
    }

    /**
     * @param \Ca\Db\Item $item
     * @return $this
     */
    public function setItem($item)
    {
        return $this->setModel($item);
    }
    
}