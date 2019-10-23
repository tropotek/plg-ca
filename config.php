<?php
use Tk\Routing\Route;

$config = \App\Config::getInstance();

/** @var \Composer\Autoload\ClassLoader $composer */
$composer = $config->getComposer();
if ($composer)
    $composer->add('Ct\\', dirname(__FILE__));

$routes = $config->getRouteAssessment();
if (!$routes) return;

$params = array();

// Staff Only
$routes->add('ct-staff-assessment-manager', Route::create('/staff/{subjectCode}/ct/assessmentManager.html', 'Ct\Controller\Assessment\Manager::doDefault', $params));
$routes->add('ct-staff-assessment-edit', Route::create('/staff/{subjectCode}/ct/assessmentEdit.html', 'Ct\Controller\Assessment\Edit::doDefault', $params));

// TODO: These 2 need to be refactored down to one category table and use a parent or competantcy ????? not sure yet
$routes->add('ct-staff-domain-manager', Route::create('/staff/{subjectCode}/ct/domainManager.html', 'Ct\Controller\Domain\Manager::doDefault', $params));
$routes->add('ct-staff-domain-edit', Route::create('/staff/{subjectCode}/ct/domainEdit.html', 'Ct\Controller\Domain\Edit::doDefault', $params));

$routes->add('ct-staff-category-manager', Route::create('/staff/{subjectCode}/ct/categoryManager.html', 'Ct\Controller\Category\Manager::doDefault', $params));
$routes->add('ct-staff-category-edit', Route::create('/staff/{subjectCode}/ct/categoryEdit.html', 'Ct\Controller\Category\Edit::doDefault', $params));
// TODO: -----------------------------------------------------------------------------------------------------------

// there should be a number of scale types that an item selects as its type...
$routes->add('ct-staff-scale-manager', Route::create('/staff/{subjectCode}/ct/scaleManager.html', 'Ct\Controller\Scale\Manager::doDefault', $params));
$routes->add('ct-staff-scale-edit', Route::create('/staff/{subjectCode}/ct/scaleEdit.html', 'Ct\Controller\Scale\Edit::doDefault', $params));

// Competancy
$routes->add('ct-staff-item-manager', Route::create('/staff/{subjectCode}/ct/itemManager.html', 'Ct\Controller\Item\Manager::doDefault', $params));
$routes->add('ct-staff-item-edit', Route::create('/staff/{subjectCode}/ct/itemEdit.html', 'Ct\Controller\Item\Edit::doDefault', $params));

$routes->add('ct-staff-entry-manager', Route::create('/staff/{subjectCode}/ct/entryManager.html', 'Ct\Controller\Entry\Manager::doDefault', $params));
$routes->add('ct-staff-entry-edit', Route::create('/staff/{subjectCode}/ct/entryEdit.html', 'Ct\Controller\Entry\Edit::doDefault', $params));
$routes->add('ct-staff-entry-view', Route::create('/staff/{subjectCode}/ct/entryView.html', 'Ct\Controller\Entry\View::doDefault', $params));

// TODO: All these need to be reviewed
//$routes->add('ct-staff-report-staff-entry-results', Route::create('/staff/{subjectCode}/ct/entryResults.html', 'Ct\Controller\Report\StudentResults::doDefault', $params));
//$routes->add('ct-staff-report-entry', Route::create('/staff/{subjectCode}/ct/assessmentReport.html', 'Ct\Controller\Report\AssessmentReport::doDefault', $params));
//$routes->add('ct-staff-report-historic-all', Route::create('/staff/{subjectCode}/ct/historicReportAll.html', 'Ct\Controller\Report\HistoricReportAll::doDefault', $params));
//$routes->add('ct-staff-report-historic', Route::create('/staff/{subjectCode}/ct/historicReport.html', 'Ct\Controller\Report\HistoricReport::doDefault', $params));
//$routes->add('ct-staff-report-date-average', Route::create('/staff/{subjectCode}/ct/dateAverageReport.html', 'Ct\Controller\Report\DateAverageReport::doDefault', $params));
//$routes->add('ct-staff-report-item-average', Route::create('/staff/{subjectCode}/ct/itemAverageReport.html', 'Ct\Controller\Report\ItemAverageReport::doDefault', $params));
//$routes->add('ct-staff-report-company-average', Route::create('/staff/{subjectCode}/ct/companyAverageReport.html', 'Ct\Controller\Report\CompanyAverageReport::doDefault', $params));

// Student Only
//$params = array('role' => array('student'));
$routes->add('ct-student-entry-edit', Route::create('/student/{subjectCode}/ct/entryEdit.html', 'Ct\Controller\Entry\Edit::doDefault', $params));
$routes->add('ct-student-entry-view', Route::create('/student/{subjectCode}/ct/entryView.html', 'Ct\Controller\Entry\View::doDefault', $params));
$routes->add('ct-student-entry-results', Route::create('/student/{subjectCode}/ct/entryResults.html', 'Ct\Controller\Report\StudentResults::doDefault', $params));

// Guest Pages
// TODO: We also need to review this, would be good to make it secure somehow to stop students from seeing it.
$routes->add('ct-public-entry-submit', Route::create('/inst/{institutionHash}/ct/catEdit.html', 'Ct\Controller\Entry\Edit::doPublicSubmission'));


