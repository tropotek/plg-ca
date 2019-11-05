<?php
namespace Ca\Db;

use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;
use Bs\Db\Mapper;
use Tk\Db\Filter;

/**
 * @author Mick Mifsud
 * @created 2019-11-05
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class ItemMap extends Mapper
{

    /**
     * @return \Tk\DataMap\DataMap
     */
    public function getDbMap()
    {
        if (!$this->dbMap) { 
            $this->setTable('ca_item');

            $this->dbMap = new \Tk\DataMap\DataMap();
            $this->dbMap->addPropertyMap(new Db\Integer('id'), 'key');
            $this->dbMap->addPropertyMap(new Db\Text('uid'));
            $this->dbMap->addPropertyMap(new Db\Integer('assessmentId', 'assessment_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('scaleId', 'scale_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('domainId', 'domain_id'));
            $this->dbMap->addPropertyMap(new Db\Text('name'));
            $this->dbMap->addPropertyMap(new Db\Text('description'));
            $this->dbMap->addPropertyMap(new Db\Boolean('gradable'));
            $this->dbMap->addPropertyMap(new Db\Integer('orderBy', 'order_by'));
            $this->dbMap->addPropertyMap(new Db\Date('modified'));
            $this->dbMap->addPropertyMap(new Db\Date('created'));

        }
        return $this->dbMap;
    }

    /**
     * @return \Tk\DataMap\DataMap
     */
    public function getFormMap()
    {
        if (!$this->formMap) {
            $this->formMap = new \Tk\DataMap\DataMap();
            $this->formMap->addPropertyMap(new Form\Integer('id'), 'key');
            $this->formMap->addPropertyMap(new Form\Text('uid'));
            $this->formMap->addPropertyMap(new Form\Integer('assessmentId'));
            $this->formMap->addPropertyMap(new Form\Integer('scaleId'));
            $this->formMap->addPropertyMap(new Form\Integer('domainId'));
            $this->formMap->addPropertyMap(new Form\Text('name'));
            $this->formMap->addPropertyMap(new Form\Text('description'));
            $this->formMap->addPropertyMap(new Form\Boolean('gradable'));
            $this->formMap->addPropertyMap(new Form\Integer('orderBy'));
            $this->formMap->addPropertyMap(new Form\Date('modified'));
            $this->formMap->addPropertyMap(new Form\Date('created'));

        }
        return $this->formMap;
    }

    /**
     * @param array|Filter $filter
     * @param Tool $tool
     * @return ArrayObject|Item[]
     * @throws \Exception
     */
    public function findFiltered($filter, $tool = null)
    {
        return $this->selectFromFilter($this->makeQuery(\Tk\Db\Filter::create($filter)), $tool);
    }

    /**
     * @param Filter $filter
     * @return Filter
     */
    public function makeQuery(Filter $filter)
    {
        $filter->appendFrom('%s a', $this->quoteParameter($this->getTable()));

        if (!empty($filter['keywords'])) {
            $kw = '%' . $this->escapeString($filter['keywords']) . '%';
            $w = '';
            //$w .= sprintf('a.name LIKE %s OR ', $this->quote($kw));
            if (is_numeric($filter['keywords'])) {
                $id = (int)$filter['keywords'];
                $w .= sprintf('a.id = %d OR ', $id);
            }
            if ($w) $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['id'])) {
            $filter->appendWhere('a.id = %s AND ', (int)$filter['id']);
        }
        if (!empty($filter['uid'])) {
            $filter->appendWhere('a.uid = %s AND ', $this->quote($filter['uid']));
        }
        if (!empty($filter['assessmentId'])) {
            $filter->appendWhere('a.assessment_id = %s AND ', (int)$filter['assessmentId']);
        }
        if (!empty($filter['scaleId'])) {
            $filter->appendWhere('a.scale_id = %s AND ', (int)$filter['scaleId']);
        }
        if (!empty($filter['domainId'])) {
            $filter->appendWhere('a.domain_id = %s AND ', (int)$filter['domainId']);
        }
        if (!empty($filter['name'])) {
            $filter->appendWhere('a.name = %s AND ', $this->quote($filter['name']));
        }
        if (!empty($filter['gradable'])) {
            $filter->appendWhere('a.gradable = %s AND ', (int)$filter['gradable']);
        }
        if (!empty($filter['orderBy'])) {
            $filter->appendWhere('a.order_by = %s AND ', (int)$filter['orderBy']);
        }


        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }

}