<?php

namespace Ubirimi\Yongo\Controller\Issue\LogWork;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\UbirimiController;
use Ubirimi\Util;
use Ubirimi\Yongo\Repository\Issue\Issue;
use Ubirimi\Yongo\Repository\Issue\WorkLog;

class EditController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();
        $workLogId = $request->request->get('id');
        $issueId = $request->request->get('issue_id');
        $timeSpent = trim(str_replace(" ", '', $request->request->get('time_spent')));
        $dateStartedString = $request->request->get('date_started');
        $remainingTimePost = $request->request->get('remaining');
        $comment = $request->request->get('comment');

        $currentDate = Util::getServerCurrentDateTime();
        $dateStarted = \DateTime::createFromFormat('d-m-Y H:i', $dateStartedString);
        $dateStartedString = date_format($dateStarted, 'Y-m-d H:i');

        $workLog = $this->getRepository(WorkLog::class)->getById($workLogId);

        $this->getRepository(WorkLog::class)->updateLogById($workLogId, $timeSpent, $dateStartedString, $comment);

        $issueQueryParameters = array('issue_id' => $issueId);
        $issue = $this->getRepository(Issue::class)->getByParameters($issueQueryParameters, $session->get('user/id'));

        $remaining = $this->getRepository(WorkLog::class)->adjustRemainingEstimate(
            $issue,
            null,
            "+" . $workLog['time_spent'],
            $session->get('yongo/settings/time_tracking_hours_per_day'),
            $session->get('yongo/settings/time_tracking_days_per_week'),
            $session->get('user/id')
        );

        $previousIssueRemainingEstimate = $issue['remaining_estimate'];

        $issue['remaining_estimate'] = $remaining;

        $remainingTimePost = $this->getRepository(WorkLog::class)->adjustRemainingEstimate(
            $issue,
            $timeSpent,
            $remainingTimePost,
            $session->get('yongo/settings/time_tracking_hours_per_day'),
            $session->get('yongo/settings/time_tracking_days_per_week'),
            $session->get('user/id')
        );

        // update the history
        $currentDate = Util::getServerCurrentDateTime();
        $fieldChanges = array(
            array('time_spent', $workLog['time_spent'], $timeSpent),
            array('remaining_estimate', $previousIssueRemainingEstimate, $remainingTimePost)
        );

        $this->getRepository(Issue::class)->updateHistory($issue['id'], $session->get('user/id'), $fieldChanges, $currentDate);

        // update the date_updated field
        $this->getRepository(Issue::class)->updateById($issueId, array('date_updated' => $currentDate), $currentDate);

        return new Response($remainingTimePost);
    }
}
