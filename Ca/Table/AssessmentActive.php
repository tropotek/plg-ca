<?php
namespace Ca\Table;

use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   $table = new Assessment::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 * 
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class AssessmentActive extends \Uni\TableIface
{
    
    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {
        $this->appendCell(new Cell\Checkbox('id'));
        $this->appendCell(new Cell\Text('icon'))->addOnCellHtml(function ($cell, $obj, $html) {
                /* @var $obj \Ca\Db\Assessment */
            $ico = 'fa fa-file-o';
            if ($obj->getIcon())
                $ico = $obj->getIcon();
            return sprintf('<i class="%s"></i>', $ico);
        });
        $this->appendCell(new Cell\Text('name'))->addCss('key')->setUrl($this->getEditUrl());

        $this->appendCell(new Cell\Text('activeSubject'))->setLabel('Subject Active')
            ->addOnCellHtml(function ($cell, $obj, $html) {
                /* @var $cell Cell\Text */
                /* @var $obj \Ca\Db\Assessment */
                $subject = \Uni\Config::getInstance()->getSubject();
                $name = 'act-'.$obj->getId();
                $cell->addCss('text-center');
                $checked = '';
                if (\Ca\Db\AssessmentMap::create()->hasSubject($subject->getId(), $obj->getId()))
                    $checked = 'checked="checked"';

                $html = sprintf('<input type="checkbox" class="ca-control ca-cb" name="%s" id="fid-%s" data-assessment-id="%s" value="%s" %s/>',
                    $name, $name, $obj->getId(), $name, $checked);
                return $html;
            });
//        $this->appendCell(new Cell\Text('publishStudent'))->setLabel('Student Publish')
//            ->addOnCellHtml(function ($cell, $obj, $html) {
//                /* @var $cell Cell\Text */
//                /* @var $obj \Ca\Db\Assessment */
//                $name = 'pub-'.$obj->getId();
//                $subject = \Uni\Config::getInstance()->getSubject();
//                $value = \Ca\Db\AssessmentMap::create()->getPublishStudent($subject->getId(), $obj->getId());
//                if ($value instanceof \DateTime)
//                    $value = $value->format(\Tk\Date::$formFormat);
//
//                $html = sprintf('
//<div class="input-group">
//  <input type="text" class="form-control ca-control date" style="min-width: 100px;" name="%s" id="fid-%s" data-assessment-id="%s" value="%s" />
//  <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
//</div>
//',
//                    $name, $name, $obj->getId(), $value);
//                return $html;
//            });

        $this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::create('New Assessment', 'fa fa-plus', \Bs\Uri::createHomeUrl('/ca/assessmentEdit.html')));
        //$this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified', 'created')));
//        $this->appendAction(\Tk\Table\Action\Delete::create());
//        $this->appendAction(\Tk\Table\Action\Csv::create());

        // load table
        //$this->setList($this->findList());

        $template = $this->getRenderer()->getTemplate();
        $js = <<<JS
jQuery(function ($) {
  
  $('.ca-cb').on('change', function () {
    var el = $(this);
    if (el.prop('checked')) {
      el.closest('tr').find('.date').removeAttr('disabled');
    } else {
      el.closest('tr').find('.date').val('').attr('disabled', 'disabled');      
    }
  }).trigger('change');
  
  $('.ca-control').on('change', function (e) {
    var tr = $(this).closest('tr');
    var active = 0;
    if (tr.find('input.ca-cb').prop('checked'))
      active = 1;
    
    $.get(document.location, {change: 'change', active: active, publish: tr.find('input.date').val(), assessmentId: $(this).data('assessmentId')}, 
      function (data) {
        //console.log(data);
      }
    );
  });
  
  
});
JS;
        $template->appendJs($js);


        
        return $this;
    }

    public function execute()
    {
        $request = $this->getConfig()->getRequest();
        if ($request->get('change')) {
            $assessmentId = (int)$request->get('assessmentId');
            if ($request->get('active')) {
                //\Ca\Db\AssessmentMap::create()->addSubject($this->getConfig()->getSubjectId(), $assessmentId);
                \Ca\Db\AssessmentMap::create()->setPublishStudent($this->getConfig()->getSubjectId(), $assessmentId,
                    \Tk\Date::createFormDate($request->get('publish')));
            } else {
                \Ca\Db\AssessmentMap::create()->removeSubject($this->getConfig()->getSubjectId(), $assessmentId);
            }
            \Tk\ResponseJson::createJson(array('status' => 'ok'))->send();
            exit();
        }

        parent::execute(); // TODO: Change the autogenerated stub
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\Ca\Db\Assessment[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \Ca\Db\AssessmentMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}