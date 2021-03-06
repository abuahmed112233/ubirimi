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

namespace Ubirimi\Yongo\Controller\Administration\Workflow\Transition\PostFunction;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;
use Ubirimi\Yongo\Repository\Workflow\Workflow;
use Ubirimi\Yongo\Repository\Workflow\WorkflowFunction;

class AddDataController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $clientId = $session->get('client/id');
        $loggedInUserId = $session->get('user/id');

        $workflowDataId = $request->get('id');
        $postFunctionId = $request->get('function_id');
        $postFunctionSelected = $this->getRepository(WorkflowFunction::class)->getById($postFunctionId);
        $workflowData = $this->getRepository(Workflow::class)->getDataById($workflowDataId);
        $workflow = $this->getRepository(Workflow::class)->getMetaDataById($workflowData['workflow_id']);

        $postFunctions = $this->getRepository(WorkflowFunction::class)->getAll();

        if ($request->request->has('add_parameters')) {

            if ($postFunctionId == WorkflowFunction::FUNCTION_SET_ISSUE_FIELD_VALUE) {
                $field_name = $request->request->get('issue_field');
                $field_value = $request->request->get('field_value');
                $value = 'field_name=' . $field_name . '###field_value=' . $field_value;

                $this->getRepository(WorkflowFunction::class)->addPostFunction($workflowDataId, $postFunctionId, $value);

                $this->getLogger()->addInfo('ADD Yongo Workflow Post Function', $this->getLoggerContext());
            }

            return new RedirectResponse('/yongo/administration/workflow/transition-post-functions/' . $workflowDataId);
        }

        $menuSelectedCategory = 'issue';
        $sectionPageTitle = $session->get('client/settings/title_name') . ' / ' . SystemProduct::SYS_PRODUCT_YONGO_NAME . ' / Create Post Function Data';

        return $this->render(__DIR__ . '/../../../../../Resources/views/administration/workflow/transition/post_function/AddData.php', get_defined_vars());
    }
}