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

namespace Ubirimi\Yongo\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\Repository\General\UbirimiClient;
use Ubirimi\Repository\User\UbirimiUser;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;
use Ubirimi\Yongo\Repository\Issue\Issue;
use Ubirimi\Yongo\Repository\Permission\GlobalPermission;
use Ubirimi\Yongo\Repository\Permission\Permission;

class IndexController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        if (Util::checkUserIsLoggedIn()) {
            $clientId = $session->get('client/id');
            $issuesPerPage = $session->get('user/issues_per_page');
            $clientSettings = $session->get('client/settings');
        } else {
            $clientId = $this->getRepository(UbirimiClient::class)->getClientIdAnonymous();
            $issuesPerPage = 25;
            $clientSettings = $this->getRepository(UbirimiClient::class)->getSettings($clientId);
        }
        $sectionPageTitle = $clientSettings['title_name'] . ' / ' . SystemProduct::SYS_PRODUCT_YONGO_NAME . ' / Dashboard';

        $selectedProjectId = $session->get('selected_project_id');
        $userAssignedId = $session->get('user/id');
        $allProjects = $this->getRepository(UbirimiClient::class)->getProjects($clientId);

        $projects = $this->getRepository(UbirimiClient::class)->getProjectsByPermission(
            $clientId,
            $session->get('user/id'),
            Permission::PERM_BROWSE_PROJECTS,
            'array'
        );

        $projectIdsArray = array();
        $projectIdsNames = array();
        $projectCount = count($projects);
        for ($i = 0; $i < $projectCount; $i++) {
            $projectIdsArray[] = $projects[$i]['id'];
            $projectIdsNames[] = array($projects[$i]['id'], $projects[$i]['name']);
        }

        $issueQueryParameters = array(
            'issues_per_page' => $issuesPerPage,
            'assignee' => $userAssignedId,
            'resolution' => array(-2),
            'sort' => 'code',
            'sort_order' => 'desc'
        );

        if (count($projectIdsArray)) {
            $issueQueryParameters['project'] = $projectIdsArray;
        } else {
            $issueQueryParameters['project'] = array(-1);
        }

        $issues = $this->getRepository(Issue::class)->getByParameters(
            $issueQueryParameters,
            $session->get('user/id'),
            null,
            $session->get('user/id')
        );

        $issueQueryParameters = array(
            'issues_per_page' => $issuesPerPage,
            'resolution' => array(-2),
            'sort' => 'code',
            'sort_order' => 'desc',
            'date_created_after' => date('Y-m-d H:i:s', strtotime("-90 days"))
        );

        if (count($projectIdsArray)) {
            $issueQueryParameters['project'] = $projectIdsArray;
        }

        if ($session->get('user/id')) {
            $issueQueryParameters['not_assignee'] = $userAssignedId;
        }

        $issuesUnresolvedOthers = $this->getRepository(Issue::class)->getByParameters(
            $issueQueryParameters,
            $session->get('user/id'),
            null,
            $session->get('user/id')
        );

        $menuSelectedCategory = 'home';

        $hasGlobalAdministrationPermission = $this->getRepository(UbirimiUser::class)->hasGlobalPermission(
            $clientId,
            $session->get('user/id'),
            GlobalPermission::GLOBAL_PERMISSION_YONGO_ADMINISTRATORS
        );

        $hasGlobalSystemAdministrationPermission = $this->getRepository(UbirimiUser::class)->hasGlobalPermission(
            $clientId,
            $session->get('user/id'),
            GlobalPermission::GLOBAL_PERMISSION_YONGO_SYSTEM_ADMINISTRATORS
        );

        $session->set('selected_product_id', SystemProduct::SYS_PRODUCT_YONGO);

        return $this->render(__DIR__ . '/../Resources/views/Index.php', get_defined_vars());
    }
}