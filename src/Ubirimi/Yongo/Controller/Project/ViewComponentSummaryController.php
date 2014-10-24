<?php

namespace Ubirimi\Yongo\Controller\Project;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\Repository\General\UbirimiClient;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;
use Ubirimi\Yongo\Repository\Issue\Issue;
use Ubirimi\Yongo\Repository\Project\YongoProject;

class ViewComponentSummaryController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        if (Util::checkUserIsLoggedIn()) {
            $loggedInUserId = $session->get('user/id');
            $clientId = $session->get('client/id');
            $clientSettings = $session->get('client/settings');
        } else {
            $clientId = $this->getRepository(UbirimiClient::class)->getClientIdAnonymous();
            $loggedInUserId = null;
            $clientSettings = $this->getRepository(UbirimiClient::class)->getSettings($clientId);
        }

        $componentId = $request->get('id');
        $component = $this->getRepository(YongoProject::class)->getComponentById($componentId);

        $projectId = $component['project_id'];

        $project = $this->getRepository(YongoProject::class)->getById($projectId);

        if ($project['client_id'] != $clientId) {
            return new RedirectResponse('/general-settings/bad-link-access-denied');
        }

        $menuSelectedCategory = 'project';

        $sectionPageTitle = $clientSettings['title_name'] . ' / ' . SystemProduct::SYS_PRODUCT_YONGO_NAME . ' / Component: ' . $component['name'] . ' / Summary';
        $issuesResult = $this->getRepository(Issue::class)->getByParameters(array('project' => $projectId,
            'resolution' => array(-2),
            'page' => 1,
            'component' => array($componentId),
            'issues_per_page' => 10), $loggedInUserId, null, $loggedInUserId);
        $issues = $issuesResult[0];

        $issuesResultUpdatedRecently = $this->getRepository(Issue::class)->getByParameters(array('project' => $projectId,
            'resolution' => array(-2),
            'page' => 1,
            'issues_per_page' => 10,
            'sort' => 'updated',
            'component' => array($componentId),
            'sort_order' => 'desc'), $loggedInUserId, null, $loggedInUserId);
        $issuesUpdatedRecently = $issuesResultUpdatedRecently[0];

        return $this->render(__DIR__ . '/../../Resources/views/project/ViewComponentSummary.php', get_defined_vars());
    }
}
