<?php
namespace Ca\Controller\Competency;

use App\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('ca-competency-edit', Route::create('/staff/ca/competencyEdit.html', 'Ca\Controller\Competency\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \Ca\Db\Competency
     */
    protected $competency = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Competency Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->competency = new \Ca\Db\Competency();
        if ($request->get('competencyId')) {
            $this->competency = \Ca\Db\CompetencyMap::create()->find($request->get('competencyId'));
        }

        $this->setForm(\Ca\Form\Competency::create()->setModel($this->competency));
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
<div class="tk-panel" data-panel-title="Competency Edit" data-panel-icon="fa fa-leaf" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}