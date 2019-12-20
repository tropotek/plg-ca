<?php
namespace Ca\Controller\Entry;

use App\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
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
     *
     * @return \App\Db\Placement
     */
    public function getPlacement()
    {
        return $this->entry->getPlacement();
    }

    /**
     * @return \Ca\Db\Entry
     */
    public function getEntry()
    {
        return $this->entry;
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
            $this->getEntry()->setAssessorId($this->getUser()->getId());
        }
        $this->getEntry()->setSubjectId((int)$request->get('subjectId'));
        $this->getEntry()->setAssessmentId((int)$request->get('assessmentId'));
        $this->getEntry()->setPlacementId((int)$request->get('placementId'));
        if ($this->getEntry()->getPlacement()) {
            $this->getEntry()->setStudentId($this->getEntry()->getPlacement()->getUserId());
            if ($this->getEntry()->getAssessment()->isSelfAssessment()) {
                $this->getEntry()->setAssessorId($this->getEntry()->getPlacement()->getUserId());
            }
        }

        if ($request->get('entryId')) {
            $this->entry = \Ca\Db\EntryMap::create()->find($request->get('entryId'));
        } else {
            if (preg_match('/[0-9a-f]{32}/i', $request->get('h'))) {
                // EG: h=13644394c4d1473f1547513fc21d7934
                // http://ems.vet.unimelb.edu.au/assessment.html?h=13644394c4d1473f1547513fc21d7934&assessmentId=2
                $placement = \App\Db\PlacementMap::create()->findByHash($request->get('h'));
                if (!$placement) {
                    \Tk\Alert::addError('Invalid URL. Please contact your course coordinator.');
                    $this->getConfig()->getUserHomeUrl()->redirect();
                }
                $e = \Ca\Db\EntryMap::create()->findFiltered(array(
                        'assessmentId' => $request->get('assessmentId'),
                        'placementId' => $placement->getId()
                    )
                )->current();

                if ($e) {
                    $this->entry = $e;
                } else {
                    $this->getEntry()->setPlacementId($placement->getId());
                    $this->getEntry()->setStudentId($this->getEntry()->getPlacement()->userId);
                    $this->getEntry()->setSubjectId($this->getEntry()->getPlacement()->subjectId);
                    if (!$this->getEntry()->getAssessment()) {
                        throw new \Tk\Exception('Invalid Assessment. Please contact the subject coordinator.');
                    }
                }
            }

            if ($request->get('assessmentId') && $request->get('placementId')) {
                $filter = array(
                    'assessmentId' => $request->get('assessmentId'),
                    'placementId' => $request->get('placementId')
                );
                $e = \Ca\Db\EntryMap::create()->findFiltered($filter)->current();
                if ($e) $this->entry = $e;
            }
        }
            // TODO: Do not think this is needed, at any rate use the placement subjectId if needed!!!
//            if (!$this->getEntry()->getSubjectId() && $this->getSubject()) {
//                $this->getEntry()->setSubjectId($this->getSubject()->getId());
//            }


        // Staff view student self assessment
//        if ($request->get('assessmentId') && $request->get('studentId') && $this->getUser()->isStaff()) {
//            $e = \Ca\Db\EntryMap::create()->findFiltered(array(
//                    'assessmentId' => $request->get('assessmentId'),
//                    'studentId' => $request->get('studentId'))
//            )->current();
//            if ($e) $this->entry = $e;
//        }

        // Assumed to be student self assessment form
//        if (!$request->has('studentId') && !$request->has('subjectId') && $this->getUser() && $this->getUser()->isStudent()) {
//            $e = \Ca\Db\EntryMap::create()->findFiltered(array(
//                    'assessmentId' => $this->getEntry()->getAssessmentId(),
//                    'subjectId' => $this->getEntry()->getSubjectId(),
//                    'studentId' => $this->getEntry()->getStudentId())
//            )->current();
//            if ($e) $this->entry = $e;
//        }

        if ($this->isPublic()) {
            if ($this->getEntry()->hasStatus(array(\Ca\Db\Entry::STATUS_APPROVED, \Ca\Db\Entry::STATUS_NOT_APPROVED))) {
                $this->errors[] = 'This entry has already been submitted.';
                return;
            }
            if ($this->getEntry()->getPlacement() && !$this->getEntry()->getAssessment()->isAvailable($this->getEntry()->getPlacement())) {
                $this->errors[] = 'This entry is no longer available.';
                return;
            }
        } else {
            if ($this->getUser()->isStudent()) {
                if ($this->getEntry()->getId() && $this->getEntry()->getPlacement()) {
                    if (!$this->getEntry()->getAssessment()->canWriteEntry($this->getEntry()->getPlacement(), $this->getUser())) {
                        if (!$this->getEntry()->getAssessment()->canReadEntry($this->getEntry()->getPlacement(), $this->getUser())) {
                            \Tk\Alert::addError('You do not have access to this file, please contact your coordinator.');
                            \Uni\Uri::createSubjectUrl('/index.html')->redirect();
                        }
                        if ($this->getEntry()->getId()) {
                            \Uni\Uri::createSubjectUrl('/entryView.html')->set('entryId', $this->getEntry()->getId())->redirect();
                        }
                        //\Tk\Alert::addError('');

                    }
                }
            }
        }

        if (!$this->getEntry()->getId() && $this->getEntry()->getPlacement()) {
            $this->getEntry()->setTitle($this->getEntry()->getPlacement()->getTitle(true));
            if ($this->getEntry()->getPlacement()->getCompany()) {
                $this->getEntry()->setAssessorName($this->getEntry()->getPlacement()->getCompany()->getName());
                $this->getEntry()->setAssessorEmail($this->getEntry()->getPlacement()->getCompany()->getEmail());
            }
            if ($this->getEntry()->getPlacement()->getSupervisor()) {
                $this->getEntry()->setAssessorName($this->getEntry()->getPlacement()->getSupervisor()->getName());
                $this->getEntry()->setAssessorEmail($this->getEntry()->getPlacement()->getSupervisor()->getEmail());
            }
        }

        if ($this->getEntry()->getAssessment()->isSelfAssessment() && !$this->getEntry()->getId()) {
            $this->getEntry()->setTitle($this->getEntry()->getAssessment()->getName() . ': ' . $this->getEntry()->getTitle());
            $this->getEntry()->setAssessorName($this->getEntry()->getStudent()->getName());
            $this->getEntry()->setAssessorEmail($this->getEntry()->getStudent()->getEmail());
        }

        // ---------------------- End Entry Setup -------------------

        $this->setPageTitle($this->getEntry()->getAssessment()->getName());

        $this->setForm(\Ca\Form\Entry::create($this->isPublic())->setModel($this->getEntry()));
        if ($this->getEntry()->getAssessment()->isSelfAssessment() && !$this->getUser()->isStaff()) {
            $this->getForm()->removeField('assessorName');
            $this->getForm()->removeField('assessorEmail');
            $this->getForm()->removeField('average');
            $this->getForm()->removeField('absent');
        }
        $this->initForm($request);
        $this->getForm()->execute();

        if ($this->getUser() && $this->getUser()->isStaff() && $this->getEntry()->getId()) {
            $this->statusTable = \App\Table\Status::create(\App\Config::getInstance()->getUrlName().'-status')->init();
            $filter = array(
                'model' => $this->getEntry(),
                'subjectId' => $this->getEntry()->getSubjectId()
            );
            $this->statusTable->setList($this->statusTable->findList($filter, $this->statusTable->getTool('created DESC')));
        }
    }

    /**
     *
     */
    public function initActionPanel()
    {
        if ($this->getEntry()->getId() && ($this->getUser() && $this->getUser()->isStaff())) {
            // TODO: Lets implement these at a later stage
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('View',
                \Uni\Uri::createSubjectUrl('/ca/entryView.html')->set('entryId', $this->getEntry()->getId()), 'fa fa-eye'));
//            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('PDF',
//                \Uni\Uri::createSubjectUrl('/ca/entryView.html')->set('entryId', $this->getEntry()->getId())->set('p', 'p'), 'fa fa-file-pdf-o')
//                ->setAttr('target', '_blank'));
        }
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->initActionPanel();
        $template = parent::show();

        if (count($this->errors)) {
            foreach ($this->errors as $error) {
                \Tk\Alert::addWarning($error);
            }
        }

        if ($this->isPublic()) {
            if (count($this->errors)) {
                $template->setVisible('not-available');
                $template->setAttr('contact', 'href', \Tk\Uri::create('/contact.html')
                    ->set('subjectId', $this->getEntry()->getSubjectId()));
                return $template;
            } else {
                $template->setVisible('available');
            }
        } else {
            $template->setVisible('edit');
            if ($this->getUser()->isStaff()) {
                if ($this->getEntry()->getId()) {
                    if ($this->statusTable) {
                        $template->appendTemplate('statusLog', $this->statusTable->show());
                        $template->setVisible('statusLog');
                    }
                }
            }
        }

        $title = $this->getEntry()->getAssessment()->getName();
        if ($this->getEntry()->getPlacement()) {
            $title .= ': ' . $this->getEntry()->getPlacement()->getTitle(true);
        }
        if ($this->getEntry()->getId()) {
            $title = sprintf('[ID: %s] ', $this->getEntry()->getId()) . $title;
        }
        $template->setAttr('panel', 'data-panel-title', $title);

        if ($this->getEntry()->getAssessment()->getIcon()) {
            $template->setAttr('panel', 'data-panel-icon', $this->getEntry()->getAssessment()->icon);
        }
        if ($this->getEntry()->getAssessment()->getDescription()) {

            $template->insertHtml('instructions', $this->getEntry()->getAssessment()->getDescription());
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
  <div class="tk-panel" data-panel-title="Entry Edit" data-panel-icon="fa fa-question" var="panel">
      <div class="ca-description" choice="instructions" var="instructions"></div>
      <hr choice="instructions"/>
  </div>
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
        <div class="ca-description" var="instructions"></div>
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