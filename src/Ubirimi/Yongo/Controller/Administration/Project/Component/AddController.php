<?php

namespace Ubirimi\Yongo\Controller\Administration\Project\Component;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\Repository\General\UbirimiClient;
use Ubirimi\Repository\General\UbirimiLog;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;
use Ubirimi\Yongo\Repository\Project\YongoProject;


class AddController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $projectId = $request->get('id');
        $project = $this->getRepository(YongoProject::class)->getById($projectId);
        $users = $this->getRepository(UbirimiClient::class)->getUsers($session->get('client/id'));

        $emptyName = false;
        $alreadyExists = false;

        if ($request->request->has('confirm_new_component')) {
            $name = Util::cleanRegularInputField($request->request->get('name'));
            $description = Util::cleanRegularInputField($request->request->get('description'));
            $leader = Util::cleanRegularInputField($request->request->get('leader'));

            if (empty($name))
                $emptyName = true;

            $components_duplicate = $this->getRepository(YongoProject::class)->getComponentByName($projectId, $name);
            if ($components_duplicate)
                $alreadyExists = true;

            if (!$emptyName && !$alreadyExists) {
                if ($leader == -1) {
                    $leader = null;
                }
                $currentDate = Util::getServerCurrentDateTime();
                $this->getRepository(YongoProject::class)->addComponent($projectId, $name, $description, $leader, null, $currentDate);

                $this->getRepository(UbirimiLog::class)->add(
                    $session->get('client/id'),
                    SystemProduct::SYS_PRODUCT_YONGO,
                    $session->get('user/id'),
                    'ADD Project Component ' . $name,
                    $currentDate
                );

                return new RedirectResponse('/yongo/administration/project/components/' . $projectId);
            }
        }
        $menuSelectedCategory = 'project';
        $sectionPageTitle = $session->get('client/settings/title_name') . ' / ' . SystemProduct::SYS_PRODUCT_YONGO_NAME . ' / Create Project Component';

        return $this->render(__DIR__ . '/../../../../Resources/views/administration/project/component/Add.php', get_defined_vars());
    }
}
