<?php
namespace Ca\Form\Field\Traits;

use Ca\Db\Competency;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait CompetencyTrait
{

    /**
     * @var \Tk\Db\Map\ArrayObject
     */
    private $competencyList = null;

    /**
     * @return \Tk\Db\Map\ArrayObject|Competency[]
     */
    public function getCompetencyList()
    {
        return $this->competencyList;
    }

    /**
     * @param \Tk\Db\Map\ArrayObject $competencyList
     * @return $this
     */
    public function setCompetencyList($competencyList)
    {
        $this->competencyList = $competencyList;
        return $this;
    }

    public function getCompetencyHtml()
    {
        $html = '';
        if ($this->getCompetencyList() && $this->getCompetencyList()->count()) {
            $html = '<ul class="ca-competency" title="Item Competencies">';
            foreach ($this->getCompetencyList() as $competency) {
                $html .= sprintf('<li>%s</li>', $competency->getName());
            }
            $html .= '</ul>';
        }
        return $html;
    }

}