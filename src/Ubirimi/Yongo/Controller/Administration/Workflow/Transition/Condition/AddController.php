<?php

namespace Ubirimi\Yongo\Controller\Administration\Workflow\Transition\Condition;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;
use Ubirimi\Yongo\Repository\Workflow\Workflow;

class AddController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $clientId = $session->get('client/id');

        $workflowDataId = $request->get('id');
        $workflowData = $this->getRepository(Workflow::class)->getDataById($workflowDataId);
        $workflow = $this->getRepository(Workflow::class)->getMetaDataById($workflowData['workflow_id']);

        if ($workflow['client_id'] != $clientId) {
            return new RedirectResponse('/general-settings/bad-link-access-denied');
        }
        $conditions = $this->getRepository('yongo.workflow.condition')->getAll();
        $menuSelectedCategory = 'issue';
        $checkedHTML = 'checked="checked"';
        $sectionPageTitle = $session->get('client/settings/title_name') . ' / ' . SystemProduct::SYS_PRODUCT_YONGO_NAME . ' / Create Workflow Condition';

        return $this->render(__DIR__ . '/../../../../../Resources/views/administration/workflow/transition/condition/Add.php', get_defined_vars());
    }
}