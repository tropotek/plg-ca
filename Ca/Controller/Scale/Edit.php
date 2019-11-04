<?php
namespace Ca\Controller\Scale;

use App\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('ca-scale-edit', Route::create('/staff/ca/scaleEdit.html', 'Ca\Controller\Scale\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \Ca\Db\Scale
     */
    protected $scale = null;

    /**
     * @var null|\Ca\Table\Option
     */
    protected $optionTable = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Scale Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->scale = new \Ca\Db\Scale();
        if ($request->get('scaleId')) {
            $this->scale = \Ca\Db\ScaleMap::create()->find($request->get('scaleId'));
            if ($this->scale && $this->scale->getType() == \Ca\Db\Scale::TYPE_CHOICE) {

                $this->optionTable = \Ca\Table\Option::create();
                $this->optionTable->setEditUrl(\Bs\Uri::createHomeUrl('/ca/optionEdit.html'));
                $this->optionTable->init();

                $filter = array(
                    'institutionId' => $this->getConfig()->getInstitutionId()
                );
                $this->optionTable->setList($this->optionTable->findList($filter));
            }
        }

        $this->setForm(\Ca\Form\Scale::create()->setModel($this->scale));
        $this->initForm($request);
        $this->getForm()->execute();
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->appendTemplate('panel', $this->getForm()->show());

        if (!$this->scale->getId() || $this->scale->getType() != \Ca\Db\Scale::TYPE_CHOICE) {
            $template->setAttr('left-col', 'class', 'col-md-12');
            $template->setVisible('right-col', false);
        }
        if ($this->optionTable) {
            $template->appendTemplate('panel2', $this->optionTable->show());
        }
        return $template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="row">
  <div class="col-md-7" var="left-col">
    <div class="tk-panel" data-panel-title="Scale Edit" data-panel-icon="fa fa-balance-scale" var="panel"></div>
  </div>
  <div class="col-md-5" var="right-col">
    <div class="tk-panel" data-panel-title="Scale Options" data-panel-icon="fa fa-list" var="panel2"></div>
  </div>
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}