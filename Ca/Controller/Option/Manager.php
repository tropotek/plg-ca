<?php 
namespace Ca\Controller\Option;

use App\Controller\AdminManagerIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('ca-option-manager', Route::create('/staff/ca/optionManager.html', 'Ca\Controller\Option\Manager::doDefault'));
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
        $this->setPageTitle('Option Manager');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setTable(\Ca\Table\Option::create());
        $this->getTable();
            //->setEditUrl(\Bs\Uri::createHomeUrl('/ca/optionEdit.html'));
        $this->getTable()->init();

        $filter = array(
            'scaleId' => $this->getRequest()->get('scaleId')
        );
        $this->getTable()->setList($this->getTable()->findList($filter));
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
//        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('New Option',
//            $this->getTable()->getEditUrl(), 'fa fa-book fa-add-action'));
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
<div class="tk-panel" data-panel-title="Options" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }
    
}