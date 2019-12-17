<?php 
namespace Ca\Controller\Competency;

use App\Controller\AdminManagerIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('ca-competency-manager', Route::create('/staff/ca/competencyManager.html', 'Ca\Controller\Competency\Manager::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Manager extends AdminManagerIface
{

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Competency Manager');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setTable(\Ca\Table\Competency::create());
        $this->getTable();
        $this->getTable()->init();

        $filter = array(
            'institutionId' => $this->getConfig()->getInstitutionId()
        );
        $this->getTable()->setList($this->getTable()->findList($filter));
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
//        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('New Competency',
//            $this->getTable()->getEditUrl(), 'fa fa-leaf fa-add-action'));
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->initActionPanel();
        $template = parent::show();

        $template->appendTemplate('panel', $this->getTable()->show());

        return $template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-panel" data-panel-title="Competencies" data-panel-icon="fa fa-leaf" var="panel">
  <p>
    <b>NOTICE:</b> At this time the competencies are not editable, please contact the site administrator if you with to
    have your own competencies added to the list. 
  </p>
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }
    
}