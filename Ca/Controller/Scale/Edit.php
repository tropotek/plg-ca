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
            $this->assessment = \Ca\Db\ScaleMap::create()->find($request->get('scaleId'));
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

        return $template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-panel" data-panel-title="Scale Edit" data-panel-icon="fa fa-balance-scale" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}