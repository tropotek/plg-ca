<?php 
namespace Ca\Controller\Report;

use App\Controller\AdminManagerIface;
use Ca\Db\Assessment;
use Ca\Db\AssessmentMap;
use Ca\Db\OptionMap;
use Ca\Db\ScaleMap;
use Dom\Template;
use Tk\Exception;
use Tk\Request;
use Tk\Table\Cell\Summarize;
use Tk\Table\Cell\Text;

/**
 * @author Mick Mifsud
 * @created 2019-11-06
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Average extends AdminManagerIface
{

    /**
     * @var Assessment
     */
    protected $assessment = null;

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Report Assessment Average');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->assessment = AssessmentMap::create()->find($request->get('assessmentId'));
        if (!$this->assessment) {
            throw new Exception('Invalid assessment ID');
        }

        $table = $this->getConfig()->createTable('averageCalc');
        $table->setRenderer($this->getConfig()->createTableRenderer($table));
        $this->setTable($table);

        $table->appendCell(Summarize::create('name'))->addCss('key')->setOrderProperty('');
        $options = OptionMap::create()->findFiltered(['scaleId' => 4], 'value');
        foreach ($options as $option) {
            $table->appendCell(Text::create($option->getId()))->setLabel($option->getName())->setOrderProperty('')->addOnPropertyValue(function (\Tk\Table\Cell\Iface $cell, $obj, $value) {
                $cell->addCss('text-center');
                return round($value ?? 0) . '%';
            });
        }

        $this->getTable()->appendAction(\Tk\Table\Action\Csv::create('csv', ''));


        $sql = <<<SQL
SELECT *
FROM v_item_option_avg
WHERE
    assessment_id = {$this->assessment->getId()}
    AND subject_id = {$this->getSubjectId()}
ORDER BY order_by
SQL;

        $stmt = $this->getConfig()->getDb()->query($sql);
        $results = [];
        foreach ($stmt as $row) {
            $results[$row->item_id]['name'] = $row->name;
            $results[$row->item_id]['item_id'] = $row->item_id;
            $results[$row->item_id]['order_by'] = $row->order_by;
            $results[$row->item_id][$row->option_id] = $row->avg;
        }

        $this->getTable()->setList($results);
        $this->getTable()->getRenderer()->enableFooter(false);
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->initActionPanel();
        $template = parent::show();

        $template->appendTemplate('panel', $this->getTable()->getRenderer()->show());
        $placementTypes = $this->assessment->getPlacementTypes();
        $str = '';
        foreach ($placementTypes as $placementType) {
            $str .= $placementType->getName().', ';
        }
        $str = htmlspecialchars(rtrim($str, ', '));

        $template->appendText('placementTypes', $str);
        $template->appendText('assessmentName', htmlspecialchars($this->assessment->getName()));
        $template->setAttr('panel', 'data-panel-title', htmlspecialchars($this->assessment->getName()) . ' Entries [' . $str.']');
        $template->setAttr('panel', 'data-panel-icon', $this->assessment->getIcon());

        return $template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-panel" data-panel-title="Entries" data-panel-icon="fa fa-book" var="panel">
  <p>
    <b>Assessment Name: </b> <span var="assessmentName"></span><br/>
    <b>Placement Types:</b> <span var="placementTypes"></span>
  </p>

</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}