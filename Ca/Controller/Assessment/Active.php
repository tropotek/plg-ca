<?php 
namespace Ca\Controller\Assessment;

use App\Controller\AdminManagerIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('ca-assessment-manager', Route::create('/staff/ca/assessmentManager.html', 'Ca\Controller\Assessment\Manager::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2019-10-31
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Active extends AdminManagerIface
{

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Active Assessments');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setTable(\Ca\Table\AssessmentActive::create());
        //$this->getTable()->setEditUrl(\Bs\Uri::createHomeUrl('/ca/assessmentEdit.html'));
        $this->getTable()->init();

        $filter = array('courseId' => $this->getCourseId());
        $this->getTable()->setList($this->getTable()->findList($filter));
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
//        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('New Assessment',
//            $this->getTable()->getEditUrl(), 'fa fa-book fa-add-action'));

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->initActionPanel();
        $template = parent::show();

        $template->appendTemplate('panel', $this->getTable()->show());

        return $template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-panel" data-panel-title="Active Assessments" data-panel-icon="fa fa-gavel" var="panel">  
  <div>
    <h4>Activate Assessments For This Subject</h4>
    <p>
      <b>Subject Active:</b> Use this checkbox to activate assessments for this subject, Activating an assesment
      enables it for staff and companies. 
    </p>
<!--    <p>-->
<!--      <b>Student Publish</b> To make assessments available to student select a date that they can access their self assessments -->
<!--      and assessment entries from other users.-->
<!--    </p>-->
  </div>
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }
    
}