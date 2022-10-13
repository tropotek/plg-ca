<?php 
namespace Ca\Controller\Entry;

use App\Controller\AdminManagerIface;
use Ca\Db\Assessment;
use Ca\Db\AssessmentMap;
use Dom\Template;
use Tk\Exception;
use Tk\Request;
use Uni\Uri;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('ca-entry-manager', Route::create('/staff/ca/entryManager.html', 'Ca\Controller\Entry\Manager::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2019-11-06
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Manager extends AdminManagerIface
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
        $this->setPageTitle('Entry Manager');
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

        $this->setTable(\Ca\Table\Entry::create());
        $this->getTable()->setEditUrl(\Uni\Uri::createSubjectUrl('/placementEdit.html'));
        $this->getTable()->init();

        $filter = [
            'assessmentId' => $request->get('assessmentId'),
            'subjectId' => $this->getSubjectId()
        ];
        $this->getTable()->setList($this->getTable()->findList($filter));
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
        if ($this->getConfig()->isSubjectUrl()) {
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Average Report',
                Uri::createSubjectUrl('/ca/reportAverage.html')->set('assessmentId', $this->assessment->getId()), $this->assessment->getIcon()));
        }
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->initActionPanel();
        $template = parent::show();

        $template->appendTemplate('panel', $this->getTable()->show());
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