<?php
namespace Ca\Db;

use Bs\Db\Mapper;
use Tk\DataMap\Db;
use Tk\DataMap\Form;
use Tk\Db\Filter;
use Tk\Db\Map\ArrayObject;
use Tk\Db\Tool;

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
            $this->dbMap->addPropertyMap(new Db\Boolean('required'));
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
            $this->formMap->addPropertyMap(new Form\Boolean('required'));
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
            $w = $this->makeMultiQuery($filter['id'], 'a.id');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
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
        if (!empty($filter['required'])) {
            $filter->appendWhere('a.required = %s AND ', (int)$filter['required']);
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }


    // Link to competencies

    /**
     * @param int $competencyId
     * @param int $itemId
     * @return boolean
     * @throws \Exception
     */
    public function hasCompetency($competencyId, $itemId)
    {
        try {
            $stm = $this->getDb()->prepare('SELECT * FROM ca_item_competency WHERE competency_id = ? AND item_id = ?');
            $stm->bindParam(1, $competencyId);
            $stm->bindParam(2, $itemId);
            $stm->execute();
            return ($stm->rowCount() > 0);
        } catch (Exception $e) {}
        return false;
    }

    /**
     * @param int $competencyId
     * @param int $itemId (optional) If null all are to be removed
     * @throws \Exception
     */
    public function removeCompetency($competencyId, $itemId = null)
    {
        try {
            $stm = $this->getDb()->prepare('DELETE FROM ca_item_competency WHERE competency_id = ?');
            $stm->bindParam(1, $competencyId);
            if ($itemId) {
                $stm = $this->getDb()->prepare('DELETE FROM ca_item_competency WHERE competency_id = ? AND item_id = ?');
                $stm->bindParam(1, $competencyId);
                $stm->bindParam(2, $itemId);
            }
            $stm->execute();
        } catch (Exception $e) {}
    }

    /**
     * @param int $competencyId
     * @param int $itemId
     * @throws \Exception
     */
    public function addCompetency($competencyId, $itemId)
    {
        try {
            if ($this->hasCompetency($competencyId, $itemId)) return;
            $stm = $this->getDb()->prepare('INSERT INTO ca_item_competency (competency_id, item_id)  VALUES (?, ?)');
            $stm->bindParam(1, $competencyId);
            $stm->bindParam(2, $itemId);
            $stm->execute();
        } catch (Exception $e) {}
    }
}