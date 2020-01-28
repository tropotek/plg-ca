<?php
namespace Ca\Controller\Item;

use App\Controller\AdminEditIface;
use Dom\Template;
use Tk\Alert;
use Tk\Request;
use Tk\Ui\Dialog\AjaxSelect;
use Uni\Uri;

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
     * @var null|\Ca\Table\Competency
     */
    protected $table = null;

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

        if ($this->item->getId()) {
            $this->competencyDialog = AjaxSelect::create('Select Competency');
            $this->competencyDialog->setNotes('Select the competencies for this Assessment Item.');
            $this->competencyDialog->setOnSelect(array($this, 'onSelect'));
            $this->competencyDialog->setOnAjax(array($this, 'onAjax'));
            $this->competencyDialog->execute();

            $this->table = \Ca\Table\Competency::create();
            $this->table->init();
            $filter = array(
                'institutionId' => $this->getConfig()->getInstitutionId(),
                'itemId' => $this->item->getId()
            );
            $this->table->setList($this->table->findList($filter));
            $this->table->removeFilter('keywords');
            $this->table->removeAction('csv');
            $this->table->appendAction(\Tk\Table\Action\Link::createLink('Add Competency', null, 'fa fa-plus')
                ->addCss('btn-primary'))->setAttr('data-target', '#' . $this->competencyDialog->getId())->setAttr('data-toggle', 'modal');
        }
    }

    /**
     * @param Request $request
     * @return Uri
     * @throws \Exception
     */
    public function onSelect(Request $request)
    {
        $selectedCompetency = \Ca\Db\CompetencyMap::create()->find($request->get('selectedId'));
        if ($selectedCompetency) {
            try {
                \Ca\Db\ItemMap::create()->addCompetency($selectedCompetency->getId(), $this->item->getId());
                Alert::addSuccess('Added competency to assessment item.');
            } catch (\Exception $e) {
                Alert::addError('Server Error: ' . $e->getMessage());
            }
        }
        return Uri::create();
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function onAjax(Request $request)
    {
        $filter = array(
            'institutionId' => $this->getConfig()->getInstitutionId()
        );
        $filter['keywords'] = $request->get('keywords');
        $list = \Ca\Db\CompetencyMap::create()->findFiltered($filter);
        return $list->toArray();
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->appendTemplate('item-form', $this->getForm()->show());

        if ($this->table) {
            $template->appendTemplate('competency-table', $this->table->show());
            $template->setVisible('competency-table');
            $template->appendBodyTemplate($this->competencyDialog->show());
        }

        return $template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-panel" data-panel-title="Item Edit" data-panel-icon="fa fa-book" var="panel">
  <div class="item-form" var="item-form"></div>
  <div class="competency-table col-md-12" var="competency-table" choice="competency-table">
    <p>&nbsp;</p>
    <p><h4>Selected Competencies:</h4></p>
  </div>
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}