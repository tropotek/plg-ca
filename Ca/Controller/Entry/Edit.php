<?php
namespace Ca\Controller\Entry;

use App\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('ca-entry-edit', Route::create('/staff/ca/entryEdit.html', 'Ca\Controller\Entry\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2019-11-06
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \Ca\Db\Entry
     */
    protected $entry = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Entry Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->entry = new \Ca\Db\Entry();
        if ($request->get('entryId')) {
            $this->entry = \Ca\Db\EntryMap::create()->find($request->get('entryId'));
        }

        $this->setForm(\Ca\Form\Entry::create()->setModel($this->entry));
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
<div class="tk-panel" data-panel-title="Entry Edit" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}