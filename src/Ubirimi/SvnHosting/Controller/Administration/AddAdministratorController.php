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

namespace Ubirimi\SvnHosting\Controller\Administration;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\Repository\User\UbirimiUser;
use Ubirimi\SvnHosting\Repository\SvnRepository;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;

class AddAdministratorController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $clientId = $session->get('client/id');

        $menuSelectedCategory = 'svn';
        $session->set('selected_product_id', SystemProduct::SYS_PRODUCT_SVN_HOSTING);
        $regularUsers = $this->getRepository(UbirimiUser::class)->getNotSVNAdministrators($clientId);
        $noUsersSelected = false;

        if ($request->request->has('confirm_new_svn_administrator')) {
            $users = $request->request->get('user');

            if ($users) {
                $this->getRepository(SvnRepository::class)->addAdministrator($users);

                return new RedirectResponse('/svn-hosting/administration/administrators');
            } else {
                $noUsersSelected = true;
            }
        }

        return $this->render(__DIR__ . '/../../Resources/views/administration/AddAdministrator.php', get_defined_vars());
    }
}
