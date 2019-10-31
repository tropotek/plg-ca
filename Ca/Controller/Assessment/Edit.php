<?php
namespace Ca\Controller\Assessment;

use App\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('ca-assessment-edit', Route::create('/staff/ca/assessmentEdit.html', 'Ca\Controller\Assessment\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \Ca\Db\Assessment
     */
    protected $assessment = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Assessment Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->assessment = new \Ca\Db\Assessment();
        if ($request->get('assessmentId')) {
            $this->assessment = \Ca\Db\AssessmentMap::create()->find($request->get('assessmentId'));
        }

        $this->setForm(\Ca\Form\Assessment::create()->setModel($this->assessment));
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
<div class="tk-panel" data-panel-title="Assessment Edit" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}