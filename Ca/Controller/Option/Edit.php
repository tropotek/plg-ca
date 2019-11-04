<?php
namespace Ca\Controller\Option;

use App\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('ca-option-edit', Route::create('/staff/ca/optionEdit.html', 'Ca\Controller\Option\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \Ca\Db\Option
     */
    protected $option = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Option Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->option = new \Ca\Db\Option();
        if ($request->get('optionId')) {
            $this->option = \Ca\Db\OptionMap::create()->find($request->get('optionId'));
        }

        $this->setForm(\Ca\Form\Option::create()->setModel($this->option));
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
<div class="tk-panel" data-panel-title="Option Edit" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}