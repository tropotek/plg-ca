<?php
namespace Ca\Table;

use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   $table = new Scale::create();
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
class Scale extends \Bs\TableIface
{
    
    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {
    
        $this->appendCell(new Cell\Checkbox('id'));
//        $this->appendCell(new Cell\Text('uid'));
//        $this->appendCell(new Cell\Text('institutionId'));
        $url = $this->getEditUrl();
        $this->appendCell(new Cell\Text('name'))->addCss('key')->setUrl($url)
            ->setOnPropertyValue(function ($cell, $obj, $value) use ($url) {
                /** @var $cell Cell\Text */
                /** @var $obj \Ca\Db\Scale */
                if ($obj->getType() != \Ca\Db\Scale::TYPE_CHOICE)
                    $cell->setUrl(null);
                else
                    $cell->setUrl($url);

                return $value;
            });
        $this->appendCell(new Cell\Text('type'));
        $this->appendCell(new Cell\Boolean('multiple'));
        $this->appendCell(new Cell\Text('calcType'));
        $this->appendCell(new Cell\Text('maxValue'));
        $this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::create('New Scale', 'fa fa-plus', \Uni\Uri::createHomeUrl('/ca/scaleEdit.html')));
        //$this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified', 'created')));
//        $this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());

        // load table
        //$this->setList($this->findList());
        
        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\Ca\Db\Scale[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \Ca\Db\ScaleMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}