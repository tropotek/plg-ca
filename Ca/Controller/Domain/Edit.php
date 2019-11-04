<?php
namespace Ca\Controller\Domain;

use App\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('ca-domain-edit', Route::create('/staff/ca/domainEdit.html', 'Ca\Controller\Domain\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \Ca\Db\Domain
     */
    protected $domain = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Domain Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->domain = new \Ca\Db\Domain();
        if ($request->get('domainId')) {
            $this->domain = \Ca\Db\DomainMap::create()->find($request->get('domainId'));
        }

        $this->setForm(\Ca\Form\Domain::create()->setModel($this->domain));
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
<div class="tk-panel" data-panel-title="Domain Edit" data-panel-icon="fa fa-black-tie" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}