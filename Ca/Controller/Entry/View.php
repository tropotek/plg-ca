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
class View extends AdminEditIface
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
        $this->setPageTitle('Entry View');
        if ($this->getAuthUser() && $this->getAuthUser()->isStudent()) {
            $this->getActionPanel()->setEnabled(false);
        }
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
    public function doDefault(Request $request)
    {
        $this->entry = \Ca\Db\EntryMap::create()->find($request->get('entryId'));

        $this->setPageTitle('View ' . $this->getEntry()->getAssessment()->getName());

        $this->setForm(\Ca\Form\Entry::create()->setModel($this->getEntry()));
        if ($this->getEntry()->getAssessment()->isSelfAssessment() && !$this->getAuthUser()->isStaff()) {
            $this->getForm()->removeField('assessorName');
            $this->getForm()->removeField('assessorEmail');
            $this->getForm()->removeField('average');
            $this->getForm()->removeField('absent');
        }
        $this->getForm()->removeField('save');
        $this->getForm()->removeField('update');
        $this->getForm()->removeField('cancel');
        foreach ($this->getForm()->getFieldList() as $field) {
            $field->setDisabled();
        }

        $this->initForm($request);
        $this->getForm()->execute();

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $title = $this->getEntry()->getAssessment()->getName();
        if ($this->getEntry()->getPlacement()) {
            $title .= ': ' . $this->getEntry()->getPlacement()->getTitle(true);
        }
        if ($this->getEntry()->getId()) {
            $title = sprintf('[ID: %s] ', $this->getEntry()->getId()) . $title;
        }
        $template->setAttr('panel', 'data-panel-title', $title);

        if ($this->getEntry()->getAssessment()->getIcon()) {
            $template->setAttr('panel', 'data-panel-icon', $this->getEntry()->getAssessment()->getIcon());
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

}