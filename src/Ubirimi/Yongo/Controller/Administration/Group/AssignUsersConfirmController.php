<?php

namespace Ubirimi\Yongo\Controller\Administration\Group;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\UbirimiController;
use Ubirimi\Util;
use Ubirimi\Repository\Group\Group;
use Ubirimi\Repository\Client;

class AssignUsersConfirmController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $groupId = $request->get('group_id');

        $group = Group::getMetadataById($groupId);
        $allUsers = Client::getUsers($session->get('client/id'));
        $groupUsers = Group::getDataByGroupId($groupId);

        $groupUsersArrayIds = array();

        while ($groupUsers && $user = $groupUsers->fetch_array(MYSQLI_ASSOC)) {
            $groupUsersArrayIds[] = $user['user_id'];
        }

        if ($groupUsers) {
            $groupUsers->data_seek(0);
        }

        $firstSelected = true;

        return $this->render(__DIR__ . '/../../../Resources/views/administration/group/AssignUsersConfirm.php', get_defined_vars());
    }
}