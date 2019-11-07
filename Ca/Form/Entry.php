<?php
namespace Ca\Form;

use Ca\Db\CompetencyMap;
use Ca\Db\Option;
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
class Entry extends \Uni\FormIface
{
    const MODE_PRIVATE = 'private';         // For logged in users
    const MODE_PUBLIC = 'public';           // For public users

    protected $mode = 'private';

    /**
     * @throws \Exception
     */
    public function init()
    {
        $this->addCss('ca-entry-edit');

        $fieldset = 'Entry Details';
        if ($this->getUser()->isStaff() && !$this->isPublic()) {
            $this->appendField(new Field\Input('title'))->setFieldset($fieldset);
        } else {
            $this->appendField(new Field\Html('title'))->setFieldset($fieldset);
        }
        if (!$this->isPublic()) {
            $avg = $this->getEntry()->getAverage();
            $this->appendField(new Field\Html('average', sprintf('%.2f', $this->getEntry()->getAverage())))->setFieldset($fieldset);
        }
        if ($this->getUser()->isStaff() && !$this->isPublic()) {
            $this->appendField(new \App\Form\Field\CheckSelect('status', \Ca\Db\Entry::getStatusList($this->getEntry()->getStatus())))
                ->setRequired()->prependOption('-- Status --', '')->setNotes('Set the status. Use the checkbox to disable notification emails.')->setFieldset($fieldset);
        } else {
            $this->appendField(new \Tk\Form\Field\Html('status'))->setFieldset($fieldset);
        }

        // $this->appendField(new Field\Input('title'));        // HTML from auto gen title
        $this->appendField(new Field\Input('assessorName'))->setFieldset($fieldset)->setRequired();
        $this->appendField(new Field\Input('assessorEmail'))->setFieldset($fieldset);
        if ($this->getEntry()->getAssessorId() != $this->getEntry()->getStudentId()) {
            $this->appendField(new Field\Input('absent'))->setFieldset($fieldset)->setNotes('Enter the number of days absent if any.');
        }
        $this->appendField(new Field\Textarea('notes'))->setLabel('Comments')->setFieldset($fieldset);

        $items = \Ca\Db\ItemMap::create()->findFiltered(array('assessmentId' => $this->getEntry()->getAssessmentId()),
            \Tk\Db\Tool::create('order_by'));

        $fieldset = '';
        /** @var \Ca\Db\Item $item */
        foreach ($items as $item) {
            if ($item->getDomain()) {
                if ($item->getDomain()->getName() != $fieldset)
                    $fieldset = $item->getDomain()->getName();
            } else {
                $fieldset = '';
            }
            if (!$fieldset) $fieldset = 'Conclusion';


            $field = \Ca\Form\Field\ItemHelper::createField($item);
            if (!$field) continue;
            $name = trim($item->getName());
            if (!$name) {
                /** @var Option $option */
                $option = CompetencyMap::create()->findFiltered(array('itemId' => $item->getId()))->current();
                if ($option) $name = $option->getName();
            }
            $field->setLabel($name);
            $this->appendField($field)->setFieldset($fieldset, 'ca-row')->setAttr('placeholder', $item->getScale()->getType());

            // TODO:
//            $val = \Skill\Db\EntryMap::create()->findValue($this->getEntry()->getId(), $item->getId());
//            if ($val) {
//                $fld->setValue($val->value);
//            }

        }

        if ($this->isPublic()) {
            $this->appendField(new Event\Submit('submit', array($this, 'doSubmit')))->addCss('btn-success')->setIconRight('fa fa-arrow-right')->addCss('pull-right')->setLabel('Submit');
            $this->appendField(new Event\Link('cancel', \App\Uri::create('/index.html')));
        } else {
            $this->appendField(new Event\Submit('update', array($this, 'doSubmit')));
            $this->appendField(new Event\Submit('save', array($this, 'doSubmit')));
            $this->appendField(new Event\Link('cancel', $this->getBackUrl()));
        }

        $template = $this->getRenderer()->getTemplate();
        $template->appendCssUrl(\Tk\Uri::create('/plugin/plg-ca/assets/styles.less'));

        $js = <<<JS
jQuery(function ($) {
  if (config.roleType === 'staff') {
    $('.ca-entry-edit .tk-form-events').clone(true).appendTo($('.ca-entry-edit fieldset.EntryDetails'));
  }
});
JS;
        $template->appendJs($js);
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
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     * @return Entry
     */
    public function setMode(string $mode): Entry
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return ($this->getMode() == self::MODE_PUBLIC);
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