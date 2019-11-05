<?php
namespace Ca\Controller\Item;

use App\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('ca-item-edit', Route::create('/staff/ca/itemEdit.html', 'Ca\Controller\Item\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2019-11-05
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \Ca\Db\Item
     */
    protected $item = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Item Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->item = new \Ca\Db\Item();
        if ($request->get('itemId')) {
            $this->item = \Ca\Db\ItemMap::create()->find($request->get('itemId'));
        }

        $this->setForm(\Ca\Form\Item::create()->setModel($this->item));
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
<div class="tk-panel" data-panel-title="Item Edit" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}