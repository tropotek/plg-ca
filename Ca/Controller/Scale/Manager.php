<?php 
namespace Ca\Controller\Scale;

use App\Controller\AdminManagerIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('ca-scale-manager', Route::create('/staff/ca/scaleManager.html', 'Ca\Controller\Scale\Manager::doDefault'));
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
        $this->setPageTitle('Scale Manager');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setTable(\Ca\Table\Scale::create());
        $this->getTable()->setEditUrl(\Bs\Uri::createHomeUrl('/ca/optionManager.html')->set('profileId', $this->getProfileId()));
            //->setEditUrl(\Bs\Uri::createHomeUrl('/ca/scaleEdit.html')->set('profileId', $this->getProfileId()));
        $this->getTable()->init();

        $filter = array();
        $this->getTable()->setList($this->getTable()->findList($filter));
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
//        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('New Scale',
//            $this->getTable()->getEditUrl(), 'fa fa-balance-scale fa-add-action'));
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
<div class="tk-panel" data-panel-title="Scales" data-panel-icon="fa fa-balance-scale" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }
    
}