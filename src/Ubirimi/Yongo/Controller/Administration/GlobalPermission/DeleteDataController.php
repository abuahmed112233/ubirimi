<?php

namespace Ubirimi\Yongo\Controller\Administration\GlobalPermission;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\Repository\General\UbirimiLog;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;
use Ubirimi\Yongo\Repository\Permission\GlobalPermission;

class DeleteDataController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $id = $request->get('id');
        $permissionData = $this->getRepository(GlobalPermission::class)->getDataById($id);
        $this->getRepository(GlobalPermission::class)->deleteById($id);

        $currentDate = Util::getServerCurrentDateTime();

        $this->getRepository(UbirimiLog::class)->add(
            $session->get('client/id'),
            SystemProduct::SYS_PRODUCT_YONGO,
            $session->get('user/id'),
            'DELETE Yongo Global Permission ' . $permissionData['permission_name'] . ' from group ' . $permissionData['name'],
            $currentDate
        );

        return new RedirectResponse('/yongo/administration/global-permissions');
    }
}
