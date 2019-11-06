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
class Preview extends AdminEditIface
{

    /**
     * @var \Ca\Db\Assessment
     */
    protected $assessment = null;

    /**
     * @var \Ca\Db\Entry
     */
    protected $entry = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Assessment Preview');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->assessment = \Ca\Db\AssessmentMap::create()->find($request->get('assessmentId'));
        if (!$this->assessment) {
            \Tk\Alert::addError('Invalid Assessment ID. Cannot preview this assessment.');
            $this->getConfig()->getBackUrl()->redirect();
        }

        // Setup dummy Entry object
        $this->entry = new \Ca\Db\Entry();
        $this->entry->setAssessmentId($this->assessment->getId());
        $this->entry->setTitle('Student Name @ Some Company [Dates] Assessment');
        $this->entry->setAssessorName($this->getUser()->getName());
        $this->entry->setAssessorEmail($this->getUser()->getEmail());
        $this->entry->setAbsent(0);

        $this->setForm(\Ca\Form\Entry::create()->setModel($this->entry));
        $this->initForm($request);
        $this->getForm()->execute();
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
//        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Items',
//            \Uni\Uri::createHomeUrl('/ca/itemManager.html')
//                ->set('profileId', $this->assessment->getCourseId())
//                ->set('assessmentId', $this->assessment->getId()),
//            'fa fa-question'));
    }


    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->initActionPanel();
        $template = parent::show();

        // Render the form
        $template->appendHtml('instructions', $this->assessment->getDescription());
        $template->appendTemplate('form', $this->getForm()->show());

        return $template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-panel tk-ca-entry tk-ca-entry-preview" data-panel-icon="fa fa-eye" var="panel">
  <div class="instructions" var="instructions"></div>
  <div var="form"></div>
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}