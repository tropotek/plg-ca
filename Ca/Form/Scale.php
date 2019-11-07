<?php
namespace Ca\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Scale::create();
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
class Scale extends \Uni\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        $this->addCss('tk-scale-form');

        $this->appendField(new Field\Input('name'));
        if (!$this->getScale()->getId()) {
            $list = array('Text' => 'text', 'Value' => 'value', 'Choice' => 'choice');
            $this->appendField(new Field\Select('type', $list))->prependOption('-- Select --', '')->setAttr('data-value', $this->getScale()->getType());
        } else {
            $this->appendField(new Field\Html('type'))->setAttr('data-value', $this->getScale()->getType());
        }
        $this->appendField(new Field\Input('maxValue'));
        $this->appendField(new Field\Checkbox('multiple'));
        $list = array('Average' => 'avg', 'Addition' => 'add');
        $this->appendField(new Field\Select('calcType', $list))->prependOption('-- Select --', '');

        $this->appendField(new Field\Textarea('description'));

        if ($this->getScale()->getId()) {
            $this->appendField(new Event\Submit('update', array($this, 'doSubmit')));
            $this->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        } else {
            $this->appendField(new Event\Submit('save', array($this, 'doSubmit')))->setLabel('Next');
        }

        $this->appendField(new Event\Link('cancel', $this->getBackUrl()));

        $this->showJs($this->getRenderer()->getTemplate());
    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function execute($request = null)
    {
        $this->load(\Ca\Db\ScaleMap::create()->unmapForm($this->getScale()));
        parent::execute($request);
    }

    /**
     * @param \Dom\Template $template
     * @return \Dom\Template
     */
    public function showJs($template)
    {
        $js = <<<JS
jQuery(function($) {
  
  $('.tk-scale-form').each(function () {
    var form = $(this);
    
    form.find('.tk-type [data-value]').on('change', function () {
      form.find('.form-group').show();
      switch($(this).data('value')) {
        case 'value':
          form.find('.tk-multiple, .tk-calctype').hide();
          break;
        case 'choice':
          form.find('.tk-maxvalue').hide();
          form.find('.tk-multiple input').trigger('change');
          break;
        default:
        case 'text':
          form.find('.tk-multiple, .tk-calctype, .tk-maxvalue').hide();
          break; 
      }
    }).trigger('change');
    
    form.find('.tk-multiple input').on('change', function () {
      if ($(this).prop("checked")) {
        form.find('.tk-calctype').show();
      } else {
        form.find('.tk-calctype').hide();
      }
    }).trigger('change');
    
  });
  
  
});
JS;
        $template->appendJs($js);

        return $template;
    }

    /**
     * @param Form $form
     * @param Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        // Load the object with form data
        \Ca\Db\ScaleMap::create()->mapForm($form->getValues(), $this->getScale());

        // Do Custom Validations

        $form->addFieldErrors($this->getScale()->validate());
        if ($form->hasErrors()) {
            return;
        }
        
        $isNew = (bool)$this->getScale()->getId();
        $this->getScale()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('scaleId', $this->getScale()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\Ca\Db\Scale
     */
    public function getScale()
    {
        return $this->getModel();
    }

    /**
     * @param \Ca\Db\Scale $scale
     * @return $this
     */
    public function setScale($scale)
    {
        return $this->setModel($scale);
    }
    
}