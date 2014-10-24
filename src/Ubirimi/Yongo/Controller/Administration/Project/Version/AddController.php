<?php

namespace Ubirimi\Yongo\Controller\Administration\Project\Version;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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

        $emptyName = false;
        $alreadyExists = false;

        if ($request->request->has('confirm_new_release')) {
            $name = Util::cleanRegularInputField($request->request->get('name'));
            $description = Util::cleanRegularInputField($request->request->get('description'));

            if (empty($name))
                $emptyName = true;

            $releasesDuplicate = $this->getRepository(YongoProject::class)->getVersionByName($projectId, $name);
            if ($releasesDuplicate)
                $alreadyExists = true;

            if (!$emptyName && !$alreadyExists) {
                $currentDate = Util::getServerCurrentDateTime();
                $this->getRepository(YongoProject::class)->addVersion($projectId, $name, $description, $currentDate);

                $this->getRepository(UbirimiLog::class)->add(
                    $session->get('client/id'),
                    SystemProduct::SYS_PRODUCT_YONGO,
                    $session->get('user/id'),
                    'ADD Project Version ' . $name,
                    $currentDate
                );

                return new RedirectResponse('/yongo/administration/project/versions/' . $projectId);
            }
        }

        $menuSelectedCategory = 'project';
        $sectionPageTitle = $session->get('client/settings/title_name') . ' / ' . SystemProduct::SYS_PRODUCT_YONGO_NAME . ' / Create Project Version';

        return $this->render(__DIR__ . '/../../../../Resources/views/administration/project/version/Add.php', get_defined_vars());
    }
}
