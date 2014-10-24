<?php

namespace Ubirimi\Yongo\Controller\Administration\Role;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\Repository\General\UbirimiLog;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;
use Ubirimi\Yongo\Repository\Permission\Role;

class AssignDefaultUsersController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $permissionRoleId = $request->request->get('role_id');
        $userArray = $request->request->get('user_arr');

        $currentDate = Util::getServerCurrentDateTime();
        $permissionRole = $this->getRepository(Role::class)->getById($permissionRoleId);
        $this->getRepository(Role::class)->deleteDefaultUsersByPermissionRoleId($permissionRoleId);
        $this->getRepository(Role::class)->addDefaultUsers($permissionRoleId, $userArray, $currentDate);

        $this->getRepository(UbirimiLog::class)->add(
            $session->get('client/id'),
            SystemProduct::SYS_PRODUCT_YONGO,
            $session->get('user/id'),
            'UPDATE Yongo Project Role ' . $permissionRole['name'] . ' Definition',
            $currentDate
        );

        return new Response('');
    }
}
