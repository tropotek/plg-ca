<?php
namespace Ca\Db;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class EntryStatusStrategy extends \App\Db\StatusStrategyInterface
{

    /**
     * return true to trigger the status change events
     *
     * @param \App\Db\Status $status
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
     * @param \App\Db\Status $status
     * @param \App\Db\MailTemplate $mailTemplate
     * @return null|\Tk\Mail\CurlyMessage
     * @throws \Exception
     */
    public function makeStatusMessage($status, $mailTemplate)
    {
        /** @var Entry $model */
        $model = $status->getModel();

        $placement = $model->getPlacement();
        if (!$placement->getPlacementType()->isNotifications()) {
            \Tk\Log::warning('PlacementType[' . $placement->getPlacementType()->getName() . '] Notifications Disabled');
            return null;
        }
        $message = \Tk\Mail\CurlyMessage::create($mailTemplate->getTemplate());
        $message->setSubject('[#'.$model->getId().'] ' . $model->getAssessment()->getName() . ' Entry ' . ucfirst($status->getName()) . ' for ' . $placement->getTitle(true) . ' ');
        $message->setFrom(\Tk\Mail\Message::joinEmail($status->getCourse()->getEmail(), $status->getSubjectName()));

        // Setup the message vars
        \App\Util\StatusMessage::setStudent($message, $placement->getUser());
        \App\Util\StatusMessage::setSupervisor($message, $placement->getSupervisor());
        \App\Util\StatusMessage::setCompany($message, $placement->getCompany());
        \App\Util\StatusMessage::setPlacement($message, $placement);

        // A`dd entry details
        $message->set('assessment::id', $model->getAssessment()->getId());
        $message->set('assessment::name', $model->getAssessment()->getName());
        $message->set('assessment::description', $model->getAssessment()->getDescription());

        $message->set('entry::id', $model->getId());
        $message->set('entry::title', $model->getTitle());
        $message->set('entry::assessor', $model->getAssessorName());
        $message->set('entry::status', $model->getStatus());
        $message->set('entry::notes', nl2br($model->getNotes(), true));


        // Add assessment blocks
        $list = \Ca\Db\AssessmentMap::create()->findFiltered(array('courseId' => $placement->getSubject()->getCourseId()));
        /* @var \Ca\Db\Assessment $assessment */
        foreach($list as $assessment) {
            $key = $assessment->getNameKey();
            if ($model->getAssessmentId() == $assessment->getId()) {
                $message->set($key, true);
            } else {
                $message->set('not'.$key, true);
            }
        }

        switch ($mailTemplate->getRecipient()) {
            case \App\Db\MailTemplate::RECIPIENT_STUDENT:
                if ($placement->getUser()) {
                    $message->addTo(\Tk\Mail\Message::joinEmail($placement->getUser()->getEmail(), $placement->getUser()->getName()));
                }
                break;
            case \App\Db\MailTemplate::RECIPIENT_COMPANY:
                if ($placement->getCompany()) {
                    $message->addTo(\Tk\Mail\Message::joinEmail($placement->getCompany()->getEmail(), $placement->getCompany()->getName()));
                }
                break;
            case \App\Db\MailTemplate::RECIPIENT_SUPERVISOR:
                if ($placement->getSupervisor() && $placement->getSupervisor()->getEmail())
                    $message->addTo(\Tk\Mail\Message::joinEmail($placement->getSupervisor()->getEmail(), $placement->getSupervisor()->getName()));
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

        return $message;
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