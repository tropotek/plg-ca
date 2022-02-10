<?php
namespace Ca\Listener;

use Ca\Db\Assessment;
use Ca\Db\Entry;
use Ca\Plugin;
use Dom\Template;
use Tk\ConfigTrait;
use Tk\Event\Subscriber;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class AssessmentUnitsHandler implements Subscriber
{
    use ConfigTrait;

    /**
     * @param \Tk\Event\Event $event
     * @throws \Exception
     */
    public function onShow(\Tk\Event\Event $event)
    {
        if (!class_exists('\\Rs\\Calculator')) return;
        /** @var \App\Ui\StudentAssessment $studentAssessment */
        $studentAssessment = $event->get('studentAssessment');// see if the placement check function is enabled in the course settings
        $subject = $studentAssessment->getSubject();
        if (!\Rs\Plugin::getInstance()->isZonePluginEnabled(Plugin::ZONE_COURSE, $subject->getCourseId())) return;
        if (!Plugin::getInstance()->isZonePluginEnabled(Plugin::ZONE_COURSE, $subject->getCourseId())) return;
        if (!$subject->getCourse()->getData()->get('placementCheck', '') || $this->getConfig()->getAuthUser()->isStudent()) return;


        /** @var \App\Db\Placement $placement */
        foreach($studentAssessment->getPlacementList() as $placement) {
            $html = '';
            if (!Entry::isPlacementCreditEqualAssessmentClass($placement, Assessment::ASSESSOR_GROUP_COMPANY)) {
                vd(Entry::getAssessmentScaleValue($placement, Assessment::ASSESSOR_GROUP_COMPANY));
            }
            if (!Entry::isPlacementCreditEqualAssessmentClass($placement, Assessment::ASSESSOR_GROUP_STUDENT)) {
                vd(Entry::getAssessmentScaleValue($placement, Assessment::ASSESSOR_GROUP_STUDENT));
            }
        }
        //vd($studentAssessment->getUnitCols());


//        $calc = \Rs\Calculator::createFromPlacementList($studentAssessment->getPlacementList());
//        if (!$calc) return;
//        $ruleList = $calc->getRuleList();
//
//        $label = $calc->getSubject()->getCourseProfile()->getUnitLabel();
//        $totals = $calc->getRuleTotals();
//
//        //vd($ruleList->toArray('name'));
//
//        /** @var \Rs\Db\Rule $rule */
//        foreach ($ruleList as $i => $rule) {
//            $t = $totals[$rule->getLabel()];
//            $studentAssessment->addTotal('Total', $rule->getLabel(), $t['total'], $this->getValidCss($t['validTotal']), $t['validMsg']);
//        }
//        $studentAssessment->addTotal('Total', $label, $totals['total']['total'],
//            $this->getValidCss($totals['total']['validTotal']), $totals['total']['validMsg']);
//
//        //vd($totals);
//
//        $event->stopPropagation();
    }

//    private function getValidCss($validValue)
//    {
//        if ($validValue < 0) return 'less';
//        if ($validValue > 0) return 'grater';
//        return 'equal';
//    }

    /**
     * getSubscribedEvents
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \App\UiEvents::STUDENT_ASSESSMENT_SHOW => array('onShow', 10)
        );
    }

}


