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
     * @var \App\Db\Placement
     */
    protected $placement = null;

    /**
     * @var \Ca\Db\Entry
     */
    protected $entry = null;

    /**
     * @var \App\Table\Status
     */
    protected $statusTable = null;

    /**
     * @var bool
     */
    protected $public = false;

    /**
     * @var array
     */
    protected $errors = array();


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Entry Edit');
        if ($this->getUser() && $this->getUser()->isStudent()) {
            $this->getActionPanel()->setEnabled(false);
        }
    }

    public function isPublic()
    {
        return $this->public;
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doPublicSubmission(Request $request)
    {
        $this->public = true;
        $this->getActionPanel()->setEnabled(false);
        $this->setTemplate($this->__makePublicTemplate());
        $this->doDefault($request);
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        // ---------------------- Start Entry Setup -------------------
        $this->entry = new \Ca\Db\Entry();
        if ($this->getUser()) {
            $this->entry->setAssessorId($this->getUser()->getId());
        }
        $this->entry->setSubjectId((int)$request->get('subjectId'));
        $this->entry->setAssessmentId((int)$request->get('assessmentId'));
        $this->entry->setPlacementId((int)$request->get('placementId'));

        if ($request->get('entryId')) {
            $this->entry = \Ca\Db\EntryMap::create()->find($request->get('entryId'));
        }

        if (preg_match('/[0-9a-f]{32}/i', $request->get('h'))) {
            // EG: h=13644394c4d1473f1547513fc21d7934
            // http://ems.vet.unimelb.edu.au/assessment.html?h=13644394c4d1473f1547513fc21d7934&assessmentId=2
            $this->placement = \App\Db\PlacementMap::create()->findByHash($request->get('h'));
            if (!$this->placement) {
                \Tk\Alert::addError('Invalid URL. Please contact your course coordinator.');
                $this->getConfig()->getUserHomeUrl()->redirect();
            }
            $e = \Ca\Db\EntryMap::create()->findFiltered(array(
                    'assessmentId' => $request->get('assessmentId'),
                    'placementId' => $this->placement->getId()
                )
            )->current();

            if ($e) {
                $this->entry = $e;
            } else {
                $this->entry->setPlacementId($this->placement->getId());
                $this->entry->setStudentId($this->placement->userId);
                $this->entry->setSubjectId($this->placement->subjectId);
                if (!$this->entry->getAssessment()) {
                    throw new \Tk\Exception('Invalid Assessment. Please contact the subject coordinator.');
                }
            }
        }
        if (!$this->entry->getSubjectId() && $this->getSubject()) {
            $this->entry->setSubjectId($this->getSubject()->getId());
        }

        if ($request->get('assessmentId') && $request->get('placementId')) {
            $e = \Ca\Db\EntryMap::create()->findFiltered(array(
                    'assessmentId' => $request->get('assessmentId'),
                    'placementId' => $request->get('placementId'))
            )->current();
            if ($e) $this->entry = $e;
        }

        // Staff view student self assessment
        if ($request->get('assessmentId') && $request->get('studentId') && $this->getUser()->isStaff()) {
            $e = \Ca\Db\EntryMap::create()->findFiltered(array(
                    'assessmentId' => $request->get('assessmentId'),
                    'studentId' => $request->get('studentId'))
            )->current();
            if ($e) $this->entry = $e;
        }

        // Assumed to be student self assessment form
        if (!$request->has('studentId') && !$request->has('subjectId') && $this->getUser() && $this->getUser()->isStudent()) {
            $e = \Ca\Db\EntryMap::create()->findFiltered(array(
                    'assessmentId' => $this->entry->getAssessmentId(),
                    'subjectId' => $this->entry->getSubjectId(),
                    'studentId' => $this->entry->getStudentId())
            )->current();
            if ($e) $this->entry = $e;
        }

        if ($this->isPublic()) {
            if ($this->entry->getStatus() == \Ca\Db\Entry::STATUS_APPROVED || $this->entry->getStatus() == \Ca\Db\Entry::STATUS_NOT_APPROVED) {
                $this->errors[] = 'This entry has already been submitted.';
                return;
            }
            if ($this->entry->getPlacement() && !$this->entry->getAssessment()->isAvailable($this->entry->getPlacement())) {
                $this->errors[] = 'This entry is no longer available.';
                return;
            }
        }

        if (!$this->entry->getId() && $this->entry->getPlacement()) {
            $this->entry->setTitle($this->entry->getPlacement()->getTitle(true));
            if ($this->entry->getPlacement()->getCompany()) {
                $this->entry->setAssessorName($this->entry->getPlacement()->getCompany()->name);
                $this->entry->setAssessorEmail($this->entry->getPlacement()->getCompany()->email);
            }
            if ($this->entry->getPlacement()->getSupervisor()) {
                $this->entry->setAssessorName($this->entry->getPlacement()->getSupervisor()->name);
                $this->entry->setAssessorEmail($this->entry->getPlacement()->getSupervisor()->email);
            }
        }

        if ($this->entry->isSelfAssessment() && !$this->entry->getId()) {
            $this->entry->title = $this->entry->getCollection()->name . ' for ' . $this->entry->getUser()->getName();
            $this->entry->assessor = $this->entry->getUser()->getName();
        }

        // ---------------------- End Entry Setup -------------------


        $this->setPageTitle($this->entry->getAssessment()->name);

        $this->setForm(\Ca\Form\Entry::create()->setModel($this->entry));
        if ($this->entry->getAssessment()->isSelfAssessment()) {
            $this->getForm()->remove('assessorName');
            $this->getForm()->remove('assessorEmail');
            $this->getForm()->remove('average');
            $this->getForm()->remove('absent');
        }
        $this->initForm($request);
        $this->getForm()->execute();

        if ($this->getUser() && $this->getUser()->isStaff() && $this->entry->getId()) {
            $this->statusTable = \App\Table\Status::create(\App\Config::getInstance()->getUrlName().'-status')->init();
            $filter = array(
                'model' => $this->entry,
                'subjectId' => $this->entry->subjectId
            );
            $this->statusTable->setList($this->statusTable->findList($filter, $this->statusTable->getTool('created DESC')));
        }
    }

    /**
     *
     */
    public function initActionPanel()
    {
        if ($this->entry->getId() && ($this->getUser() && $this->getUser()->isStaff())) {
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('View',
                \App\Uri::createSubjectUrl('/ca/entryView.html')->set('entryId', $this->entry->getId()), 'fa fa-eye'));
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('PDF',
                \App\Uri::createSubjectUrl('/ca/entryView.html')->set('entryId', $this->entry->getId())->set('p', 'p'), 'fa fa-file-pdf-o')->setAttr('target', '_blank'));
        }
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->initActionPanel();
        $template = parent::show();

        if ($this->isPublic()) {
            if (count($this->errors)) {
                foreach ($this->errors as $error) {
                    \Tk\Alert::addWarning($error);
                }
                $template->setVisible('not-available');
                $template->setAttr('contact', 'href', \Tk\Uri::create('/contact.html')
                    ->set('subjectId', $this->entry->subjectId));
                return $template;
            } else {
                $template->setVisible('available');
            }
        } else {
            $template->setVisible('edit');
            if ($this->getUser()->isStaff()) {
                if ($this->entry->getId()) {
                    if ($this->statusTable) {
                        $template->appendTemplate('statusLog', $this->statusTable->show());
                        $template->setVisible('statusLog');
                    }
                }
            }
        }


        $title = $this->entry->getAssessment()->getName();
        if ($this->entry->getPlacement()) {
            $title .= ': ' . $this->entry->getPlacement()->getTitle(true);
        }
        if ($this->entry->getId()) {
            $title = sprintf('[ID: %s] ', $this->entry->getId()) . $title;
        }
        $template->setAttr('panel', 'data-panel-title', $title);

        if ($this->entry->getAssessment()->icon) {
            $template->setAttr('panel', 'data-panel-icon', $this->entry->getAssessment()->icon);
        }
        if ($this->entry->getAssessment()->getDescription()) {
            $template->insertHtml('instructions', $this->entry->getAssessment()->getDescription());
            $template->setVisible('instructions');
        }

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
<div class="EntryEdit">
  <div class="tk-panel" data-panel-title="Skill Entry Edit" data-panel-icon="fa fa-question" var="panel">
      <div class="instructions" choice="instructions" var="instructions"></div>
      <hr choice="instructions"/>
  </div>
  <div class="tk-panel" data-panel-title="Entry Edit" data-panel-icon="fa fa-book" var="panel"></div>
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makePublicTemplate()
    {
        $xhtml = <<<HTML
<div class="content EntryEdit">
  <div class="container">
    <div class="layout layout-stack-sm layout-main-left">
      <div class="layout-main" choice="available">
        <div var="instructions"></div>
        <div var="panel"></div>
      </div>
      <div class="layout-main" choice="not-available">
        <p>Please <a href="/contact.html?subjectId=0" var="contact">contact</a> the subject coordinator as this resource is no longer available.</p>
      </div>
    </div>
  </div>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }

}