<?php
namespace Ca\Db;


use App\Db\MailTemplate;
use Tk\Mail\CurlyMessage;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class EntryStatusStrategy extends \Uni\Db\StatusStrategyInterface
{

    /**
     * return true to trigger the status change events
     *
     * @param \Uni\Db\Status $status
     * @return boolean
     * @throws \Exception
     */
    public function triggerStatusChange($status)
    {
        $prevStatusName = $status->getPreviousName();
        switch($status->name) {
            case Entry::STATUS_PENDING:
                if (!$prevStatusName)
                    return true;
                break;
            case Entry::STATUS_APPROVED:
                if (!$prevStatusName || Entry::STATUS_PENDING == $prevStatusName)
                    return true;
                break;
            case Entry::STATUS_AMEND:
                if ($prevStatusName) {
                    return true;
                }
                break;
            case Entry::STATUS_NOT_APPROVED:
                if (Entry::STATUS_PENDING == $prevStatusName)
                    return true;
                break;
        }
        return false;
    }

    /**
     * @param \Uni\Db\Status $status
     * @param CurlyMessage $message
     * @throws \Exception
     */
    public function formatStatusMessage($status, $message)
    {
        /** @var Entry $entry */
        $entry = $status->getModel();
        $assessment = $entry->getAssessment();
        /** @var MailTemplate $mailTemplate */
        $mailTemplate = $message->get('_mailTemplate');

        $placement = $entry->getPlacement();
        if (!$placement->getPlacementType()->isNotifications()) {
            \Tk\Log::warning('PlacementType[' . $placement->getPlacementType()->getName() . '] Notifications Disabled');
        }

        $isReminder = false;
        if ($status->getEvent() == 'message.ca.entry.reminder')
            $isReminder = true;

        if ($isReminder && $mailTemplate->getRecipient()) {
            if ($assessment->getAssessorGroup() == Assessment::ASSESSOR_GROUP_STUDENT &&
                $mailTemplate->getRecipient() != MailTemplate::RECIPIENT_STUDENT) return;
            if ($assessment->getAssessorGroup() == Assessment::ASSESSOR_GROUP_COMPANY &&
                $mailTemplate->getRecipient() != MailTemplate::RECIPIENT_COMPANY) return;
        }


        $msgSubject = $assessment->getName() . ' Entry ' .
            ucfirst($status->getName()) . ' for ' . $placement->getTitle(true) . ' ';
        // '[#'.$entry->getId().'] '

        $message->setSubject($msgSubject);
        $message->setFrom(\Tk\Mail\Message::joinEmail($status->getCourse()->getEmail(), $status->getSubjectName()));

        // Setup the message vars
        \App\Util\StatusMessage::setStudent($message, $placement->getUser());
        \App\Util\StatusMessage::setSupervisor($message, $placement->getSupervisor());
        \App\Util\StatusMessage::setCompany($message, $placement->getCompany());
        \App\Util\StatusMessage::setPlacement($message, $placement);

        // A`dd entry details
        $message->set('_assessment', $assessment);
        $message->set('assessment::id', $assessment->getId());
        $message->set('assessment::name', $assessment->getName());
        $message->set('assessment::description', $assessment->getDescription());
        $message->set('assessment::placementTypes', $assessment->getPlacementTypeName());

        $message->set('entry::id', $entry->getId());
        $message->set('entry::title', $entry->getTitle());
        $message->set('entry::assessor', $entry->getAssessorName());
        $message->set('entry::status', $entry->getStatus());
        $message->set('entry::notes', nl2br($entry->getNotes(), true));


        // Add assessment blocks
        $list = \Ca\Db\AssessmentMap::create()->findFiltered(array('courseId' => $placement->getSubject()->getCourseId()));
        /* @var \Ca\Db\Assessment $assess */
        foreach($list as $assess) {
            $key = $assess->getNameKey();
            if ($entry->getAssessmentId() == $assess->getId()) {
                $message->set($key, true);
            } else {
                $message->set('not'.$key, true);
            }
        }


        switch ($mailTemplate->getRecipient()) {
            case \App\Db\MailTemplate::RECIPIENT_STUDENT:
                $student = $placement->getUser();
                if ($student && $student->getEmail()) {
                    $message->addTo(\Tk\Mail\Message::joinEmail($student->getEmail(), $student->getName()));
                    $message->set('recipient::email', $student->getEmail());
                    $message->set('recipient::name', $student->getName());
                }
                break;
            case \App\Db\MailTemplate::RECIPIENT_COMPANY:
                $company = $placement->getCompany();
                if ($company && $company->getEmail()) {
                    $message->addTo(\Tk\Mail\Message::joinEmail($company->getEmail(), $company->getName()));
                    $message->set('recipient::email', $company->getEmail());
                    $message->set('recipient::name', $company->getName());
                }
                break;
            case \App\Db\MailTemplate::RECIPIENT_SUPERVISOR:
                $supervisor = $placement->getSupervisor();
                if ($supervisor && $supervisor->getEmail()) {
                    $message->addTo(\Tk\Mail\Message::joinEmail($supervisor->getEmail(), $supervisor->getName()));
                    $message->set('recipient::email', $supervisor->getEmail());
                    $message->set('recipient::name', $supervisor->getName());
                }
                break;
            case \App\Db\MailTemplate::RECIPIENT_STAFF:
                $staffList = $status->getSubject()->getCourse()->getUsers();
                if (count($staffList)) {
                    /** @var \App\Db\User $s */
                    foreach ($staffList as $s) {
                        $message->addBcc(\Tk\Mail\Message::joinEmail($s->getEmail(), $s->getName()));
                    }
                    $message->addTo(\Tk\Mail\Message::joinEmail($status->getSubject()->getCourse()->getEmail(), $status->getSubjectName()));
                    $message->set('recipient::email', $status->getSubject()->getCourse()->getEmail());
                    $message->set('recipient::name', $status->getSubjectName());
                }
                break;
        }

        // This is for all recipients only
        if (!$isReminder || $mailTemplate->getRecipient()) return;

        switch ($assessment->getAssessorGroup()) {
            case Assessment::ASSESSOR_GROUP_STUDENT:
                $student = $placement->getUser();
                if ($student && $student->getEmail()) {
                    $message->addTo(\Tk\Mail\Message::joinEmail($student->getEmail(), $student->getName()));
                    $message->set('recipient::email', $student->getEmail());
                    $message->set('recipient::name', $student->getName());
                }
                break;
            case Assessment::ASSESSOR_GROUP_COMPANY:
                $company = $placement->getCompany();
                if ($company && $company->getEmail()) {
                    $message->addTo(\Tk\Mail\Message::joinEmail($company->getEmail(), $company->getName()));
                    $message->set('recipient::email', $company->getEmail());
                    $message->set('recipient::name', $company->getName());
                }
                break;
        }
    }


    /**
     * @return string
     * @throws \Exception
     */
    public function getPendingIcon()
    {
        /** @var Entry $model */
        $model = $this->getStatus()->getModel();

        $editUrl = \Uni\Uri::createSubjectUrl('/ca/entryEdit.html')->set('entryId', $model->getId());
        if (!$model->getId()) {
            $editUrl = \Uni\Uri::createSubjectUrl('/ca/entryEdit.html')->set('assessmentId', $model->getAssessmentId())->
            set('studentId', $model->getStudentId())->set('placementId', $model->getPlacementId());
        }
        return sprintf('<a href="%s"><div class="status-icon bg-secondary"><i class="'.$model->getAssessment()->getIcon().'"></i></div></a>', htmlentities($editUrl));
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getPendingHtml()
    {
        /** @var Entry $model */
        $model = $this->getStatus()->getModel();
        $editUrl = \Uni\Uri::createSubjectUrl('/ca/entryEdit.html')->set('entryId', $model->getId());
        $from = '';

        $userName = $model->getPlacement()->getUser()->getName();
        if ($model->getPlacement()) {
            $from = 'from <em>' . htmlentities($model->getPlacement()->getCompany()->getName()) . '</em>';
        }

        $html = sprintf('<div class="status-placement"><div><em>%s</em> %s submitted a %s Assessment Entry for <em>%s</em></div>
  <div class="status-actions">
    <a href="%s" class="edit"><i class="fa fa-pencil"></i> Edit</a>
   <!--  |
    <a href="#" class="view"><i class="fa fa-eye"></i> View</a> |
    <a href="#" class="approve"><i class="fa fa-check"></i> Approve</a> |
    <a href="#" class="reject"><i class="fa fa-times"></i> Reject</a> |
    <a href="#" class="email"><i class="fa fa-envelope"></i> Email</a>
    -->
  </div>
</div>',
            htmlentities($model->getAssessorName()), $from, htmlentities($model->getAssessment()->getName()), htmlentities($userName), htmlentities($editUrl));

        return $html;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getLabel()
    {
        /** @var Entry $model */
        $model = $this->getStatus()->getModel();
        return $model->getAssessment()->getName() . ' ' . \Tk\ObjectUtil::basename($this->getStatus()->getFkey());
    }
}