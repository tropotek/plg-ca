<?php
namespace Ca\Table;

use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   $table = new Item::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 * 
 * @author Mick Mifsud
 * @created 2019-11-05
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Item extends \Bs\TableIface
{
    
    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {
    
        $this->appendCell(new Cell\Checkbox('id'));
        $this->appendCell(new Cell\Text('uid'));
        $this->appendCell(new Cell\Text('assessmentId'));
        $this->appendCell(new Cell\Text('scaleId'));
        $this->appendCell(new Cell\Text('domainId'));
        $this->appendCell(new Cell\Text('name'))->addCss('key')->setUrl($this->getEditUrl());
        $this->appendCell(new Cell\Boolean('gradable'));
        $this->appendCell(new Cell\Text('orderBy'));
        $this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::createLink('New Item', \Bs\Uri::createHomeUrl('/ca/itemEdit.html'), 'fa fa-plus'));
        //$this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified', 'created')));
        $this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());

        // load table
        //$this->setList($this->findList());
        
        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\Ca\Db\Item[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \Ca\Db\ItemMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}