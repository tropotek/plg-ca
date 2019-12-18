<?php

use Tk\Routing\Route;

$config = \App\Config::getInstance();
/** @var \Composer\Autoload\ClassLoader $composer */
$composer = $config->getComposer();
if ($composer)
    $composer->add('Ca\\', dirname(__FILE__));

$routes = $config->getRouteCollection();
if (!$routes) return;


// Staff Only
$routes->add('ca-staff-assessment-manager', Route::create('/staff/ca/assessmentManager.html', 'Ca\Controller\Assessment\Manager::doDefault'));
$routes->add('ca-staff-assessment-edit', Route::create('/staff/ca/assessmentEdit.html', 'Ca\Controller\Assessment\Edit::doDefault'));
$routes->add('ca-staff-assessment-preview', Route::create('/staff/ca/assessmentPreview.html', 'Ca\Controller\Assessment\Preview::doDefault'));
$routes->add('ca-staff-assessment-active', Route::create('/staff/{subjectCode}/ca/activeAssessments.html', 'Ca\Controller\Assessment\Active::doDefault'));

$routes->add('ca-staff-domain-manager', Route::create('/staff/ca/domainManager.html', 'Ca\Controller\Domain\Manager::doDefault'));
$routes->add('ca-staff-domain-edit', Route::create('/staff/ca/domainEdit.html', 'Ca\Controller\Domain\Edit::doDefault'));

$routes->add('ca-staff-competency-manager', Route::create('/staff/ca/competencyManager.html', 'Ca\Controller\Competency\Manager::doDefault'));
$routes->add('ca-staff-competency-edit', Route::create('/staff/ca/competencyEdit.html', 'Ca\Controller\Competency\Edit::doDefault'));

$routes->add('ca-staff-scale-manager', Route::create('/staff/ca/scaleManager.html', 'Ca\Controller\Scale\Manager::doDefault'));
$routes->add('ca-staff-scale-edit', Route::create('/staff/ca/scaleEdit.html', 'Ca\Controller\Scale\Edit::doDefault'));
$routes->add('ca-staff-option-manager', Route::create('/staff/ca/optionManager.html', 'Ca\Controller\Option\Manager::doDefault'));
$routes->add('ca-staff-option-edit', Route::create('/staff/ca/optionEdit.html', 'Ca\Controller\Option\Edit::doDefault'));

$routes->add('ca-staff-score-manager', Route::create('/staff/ca/scoreManager.html', 'Ca\Controller\Score\Manager::doDefault'));
$routes->add('ca-staff-score-edit', Route::create('/staff/ca/scoreEdit.html', 'Ca\Controller\Score\Edit::doDefault'));

$routes->add('ca-staff-item-manager', Route::create('/staff/ca/itemManager.html', 'Ca\Controller\Item\Manager::doDefault'));
$routes->add('ca-staff-item-edit', Route::create('/staff/ca/itemEdit.html', 'Ca\Controller\Item\Edit::doDefault'));


//// TODO: These 2 need to be refactored down to one category table and use a parent or competantcy ????? not sure yet
//$routes->add('ca-staff-domain-manager', Route::create('/staff/{subjectCode}/ca/domainManager.html', 'Ca\Controller\Domain\Manager::doDefault'));
//$routes->add('ca-staff-domain-edit', Route::create('/staff/{subjectCode}/ca/domainEdit.html', 'Ca\Controller\Domain\Edit::doDefault'));
//
//$routes->add('ca-staff-category-manager', Route::create('/staff/{subjectCode}/ca/categoryManager.html', 'Ca\Controller\Category\Manager::doDefault'));
//$routes->add('ca-staff-category-edit', Route::create('/staff/{subjectCode}/ca/categoryEdit.html', 'Ca\Controller\Category\Edit::doDefault'));
//// TODO: -----------------------------------------------------------------------------------------------------------
//
//// there should be a number of scale types that an item selects as its type...
//$routes->add('ca-staff-scale-manager', Route::create('/staff/{subjectCode}/ca/scaleManager.html', 'Ca\Controller\Scale\Manager::doDefault'));
//$routes->add('ca-staff-scale-edit', Route::create('/staff/{subjectCode}/ca/scaleEdit.html', 'Ca\Controller\Scale\Edit::doDefault'));
//
//// Competancy
//$routes->add('ca-staff-item-manager', Route::create('/staff/{subjectCode}/ca/itemManager.html', 'Ca\Controller\Item\Manager::doDefault'));
//$routes->add('ca-staff-item-edit', Route::create('/staff/{subjectCode}/ca/itemEdit.html', 'Ca\Controller\Item\Edit::doDefault'));

$routes->add('ca-staff-entry-manager', Route::create('/staff/{subjectCode}/ca/entryManager.html', 'Ca\Controller\Entry\Manager::doDefault'));
$routes->add('ca-staff-entry-edit', Route::create('/staff/{subjectCode}/ca/entryEdit.html', 'Ca\Controller\Entry\Edit::doDefault'));
$routes->add('ca-staff-entry-view', Route::create('/staff/{subjectCode}/ca/entryView.html', 'Ca\Controller\Entry\View::doDefault'));

$routes->add('ca-student-entry-edit', Route::create('/student/{subjectCode}/ca/entryEdit.html', 'Ca\Controller\Entry\Edit::doDefault'));
$routes->add('ca-student-entry-view', Route::create('/student/{subjectCode}/ca/entryView.html', 'Ca\Controller\Entry\View::doDefault'));


// TODO: All these need to be reviewed
//$routes->add('ca-staff-report-staff-entry-results', Route::create('/staff/{subjectCode}/ca/entryResults.html', 'Ca\Controller\Report\StudentResults::doDefault'));
//$routes->add('ca-staff-report-entry', Route::create('/staff/{subjectCode}/ca/assessmentReport.html', 'Ca\Controller\Report\AssessmentReport::doDefault'));
//$routes->add('ca-staff-report-historic-all', Route::create('/staff/{subjectCode}/ca/historicReportAll.html', 'Ca\Controller\Report\HistoricReportAll::doDefault'));
//$routes->add('ca-staff-report-historic', Route::create('/staff/{subjectCode}/ca/historicReport.html', 'Ca\Controller\Report\HistoricReport::doDefault'));
//$routes->add('ca-staff-report-date-average', Route::create('/staff/{subjectCode}/ca/dateAverageReport.html', 'Ca\Controller\Report\DateAverageReport::doDefault'));
//$routes->add('ca-staff-report-item-average', Route::create('/staff/{subjectCode}/ca/itemAverageReport.html', 'Ca\Controller\Report\ItemAverageReport::doDefault'));
//$routes->add('ca-staff-report-company-average', Route::create('/staff/{subjectCode}/ca/companyAverageReport.html', 'Ca\Controller\Report\CompanyAverageReport::doDefault'));

// Student Only
//$routes->add('ca-student-entry-edit', Route::create('/student/{subjectCode}/ca/entryEdit.html', 'Ca\Controller\Entry\Edit::doDefault'));
//$routes->add('ca-student-entry-view', Route::create('/student/{subjectCode}/ca/entryView.html', 'Ca\Controller\Entry\View::doDefault'));
//$routes->add('ca-student-entry-results', Route::create('/student/{subjectCode}/ca/entryResults.html', 'Ca\Controller\Report\StudentResults::doDefault'));


// Public Pages
$routes->add('ca-public-entry-submit', Route::create('/inst/{institutionHash}/assessment.html', 'Ca\Controller\Entry\Edit::doPublicSubmission'));
// TODO: Deprecated this URL
$routes->add('ca-public-entry-submit2', Route::create('/inst/{institutionHash}/ca/skillEdit.html', 'Ca\Controller\Entry\Edit::doPublicSubmission'));


