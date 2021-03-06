<?php

/*
 *  Copyright (C) 2012-2015 SC Ubirimi SRL <info-copyright@ubirimi.com>
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License version 2 as
 *  published by the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301, USA.
 */

namespace Ubirimi\Yongo\Controller\Issue\Move;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\Container\UbirimiContainer;
use Ubirimi\LinkHelper;
use Ubirimi\Repository\User\UbirimiUser;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;
use Ubirimi\Yongo\Repository\Issue\Issue;
use Ubirimi\Yongo\Repository\Issue\IssueComponent;
use Ubirimi\Yongo\Repository\Issue\IssueSettings;
use Ubirimi\Yongo\Repository\Issue\IssueVersion;
use Ubirimi\Yongo\Repository\Project\YongoProject;

class MoveStep4Controller extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $clientId = $session->get('client/id');
        $loggedInUserId = $session->get('user/id');

        $issueId = $request->get('id');
        $issueQueryParameters = array('issue_id' => $issueId);
        $issue = $this->getRepository(Issue::class)->getByParameters($issueQueryParameters, $loggedInUserId);
        $projectId = $issue['issue_project_id'];
        $issueProject = $this->getRepository(YongoProject::class)->getById($projectId);

        // before going further, check to is if the issue project belongs to the client
        if ($clientId != $issueProject['client_id']) {
            return new RedirectResponse('/general-settings/bad-link-access-denied');
        }

        $session->set('selected_product_id', SystemProduct::SYS_PRODUCT_YONGO);

        if ($request->request->has('move_issue_step_4')) {
            $currentDate = Util::getServerCurrentDateTime();

            $oldIssueData = $this->getRepository(Issue::class)->getByParameters(array('issue_id' => $issueId), $loggedInUserId);
            $oldIssueData['component'] = $this->getRepository(IssueComponent::class)->getByIssueIdAndProjectId($issueId, $projectId, 'array', 'name');
            if ($oldIssueData['component'] == null) {
                $oldIssueData['component'] = array();
            }
            $oldIssueData['affects_version'] = $this->getRepository(IssueVersion::class)->getByIssueIdAndProjectId($issueId, $projectId, Issue::ISSUE_AFFECTED_VERSION_FLAG, 'array', 'name');
            if ($oldIssueData['affects_version'] == null) {
                $oldIssueData['affects_version'] = array();
            }
            $oldIssueData['fix_version'] = $this->getRepository(IssueVersion::class)->getByIssueIdAndProjectId($issueId, $projectId, Issue::ISSUE_FIX_VERSION_FLAG, 'array', 'name');
            if ($oldIssueData['fix_version'] == null) {
                $oldIssueData['fix_version'] = array();
            }

            $this->getRepository(IssueComponent::class)->deleteByIssueId($issueId);
            $this->getRepository(IssueVersion::class)->deleteByIssueIdAndFlag($issueId, Issue::ISSUE_FIX_VERSION_FLAG);
            $this->getRepository(IssueVersion::class)->deleteByIssueIdAndFlag($issueId, Issue::ISSUE_AFFECTED_VERSION_FLAG);

            if ($session->has('move_issue/new_assignee')) {
                $this->getRepository(Issue::class)->updateAssigneeRaw($issueId, $session->get('move_issue/new_assignee'));
            }

            if (count($session->get('move_issue/new_component'))) {
                $this->getRepository(Issue::class)->addComponentVersion($issueId, $session->get('move_issue/new_component'), 'issue_component');
            }

            if (count($session->get('move_issue/new_fix_version'))) {
                $this->getRepository(Issue::class)->addComponentVersion($issueId, $session->get('move_issue/new_fix_version'), 'issue_version', Issue::ISSUE_FIX_VERSION_FLAG);
            }

            if (count($session->get('move_issue/new_affects_version'))) {
                $this->getRepository(Issue::class)->addComponentVersion($issueId, $session->get('move_issue/new_affects_version'), 'issue_version', Issue::ISSUE_AFFECTED_VERSION_FLAG);
            }

            $newProjectId = $session->get('move_issue/new_project');

            // move the issue
            $this->getRepository(Issue::class)->move($issueId, $newProjectId, $session->get('move_issue/new_type'), $session->get('move_issue/sub_task_new_issue_type'));

            $session->remove('move_issue');
            $newIssueData = $this->getRepository(Issue::class)->getByParameters(array('issue_id' => $issueId), $loggedInUserId);
            $fieldChanges = $this->getRepository(Issue::class)->computeDifference($oldIssueData, $newIssueData, array(), array());
            $this->getRepository(Issue::class)->updateHistory($issueId, $loggedInUserId, $fieldChanges, $currentDate);

            $this->getLogger()->addInfo('MOVE Yongo issue ' . $oldIssueData['project_code'] . '-' . $oldIssueData['nr'], $this->getLoggerContext());

            UbirimiContainer::get()['issue.email']->update($clientId, $oldIssueData, $loggedInUserId, $fieldChanges);

            return new RedirectResponse(LinkHelper::getYongoIssueViewLinkJustHref($issueId));
        }

        $sectionPageTitle = $session->get('client/settings/title_name') . ' / ' . SystemProduct::SYS_PRODUCT_YONGO_NAME . ' / Move Issue - ' . $issue['project_code'] . '-' . $issue['nr'] . ' ' . $issue['summary'];

        $previousData = $session->get('move_issue');
        $menuSelectedCategory = 'issue';

        $newStatusName = $this->getRepository(IssueSettings::class)->getById($session->get('move_issue/new_status'), 'status', 'name');

        $newProject = $this->getRepository(YongoProject::class)->getById($session->get('move_issue/new_project'));
        $newTypeName = $this->getRepository(IssueSettings::class)->getById($session->get('move_issue/new_type'), 'type', 'name');

        $issueComponents = $this->getRepository(IssueComponent::class)->getByIssueIdAndProjectId($issue['id'], $projectId);
        $issueFixVersions = $this->getRepository(IssueVersion::class)->getByIssueIdAndProjectId($issue['id'], $projectId, Issue::ISSUE_FIX_VERSION_FLAG);
        $issueAffectedVersions = $this->getRepository(IssueVersion::class)->getByIssueIdAndProjectId($issue['id'], $projectId, Issue::ISSUE_AFFECTED_VERSION_FLAG);

        $newIssueComponents = null;
        $newIssueFixVersions = null;
        $newIssueAffectsVersions = null;

        if (count($session->get('move_issue/new_component'))) {
            $newIssueComponents = $this->getRepository(IssueComponent::class)->getByIds($session->get('move_issue/new_component'));
        }

        if (count($session->get('move_issue/new_fix_version'))) {
            $newIssueFixVersions = $this->getRepository(IssueVersion::class)->getByIds($session->get('move_issue/new_fix_version'));
        }

        if (count($session->get('move_issue/new_affects_version'))) {
            $newIssueAffectsVersions = $this->getRepository(IssueVersion::class)->getByIds($session->get('move_issue/new_affects_version'));
        }

        $newUserAssignee = $this->getRepository(UbirimiUser::class)->getById($session->get('move_issue/new_assignee'));

        return $this->render(__DIR__ . '/../../../Resources/views/issue/move/MoveStep4.php', get_defined_vars());
    }
}