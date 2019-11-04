<?php
namespace Ca\Table;

use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   $table = new Option::create();
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
class Option extends \Bs\TableIface
{
    /**
     * @var null
     */
    protected $optionDialog = null;


    /**
     * @param string $tableId
     */
    public function __construct($tableId = '')
    {
        parent::__construct($tableId);
    }
    
    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {
        $form = \Ca\Form\Option::create();
        $option = new \Ca\Db\Option();
        $option->setScaleId($this->getRequest()->get('scaleId'));
        $form->setOption($option);
        $this->optionDialog = \Tk\Ui\Dialog\Form::createFormDialog($form, 'Create Option');
        $this->optionDialog->execute($this->getRequest());
        $this->getRenderer()->getTemplate()->appendBodyTemplate($this->optionDialog->show());


        $this->appendCell(new Cell\Checkbox('id'));
//        $this->appendCell(new Cell\Text('scaleId'));
        $this->appendCell(new Cell\Text('name'))->addCss('key')->setUrl('#');
        $this->appendCell(new Cell\Text('value'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions

        // TODO: Add/Edit functions to the options later
        //$this->appendAction(\Tk\Table\Action\Link::createLink('New Option', null, 'fa fa-plus'))
        //    ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->optionDialog->getId());


        //$this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified', 'created')));
        //$this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());


        // load table
        //$this->setList($this->findList());
        
        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\Ca\Db\Option[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \Ca\Db\OptionMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}