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

namespace Ubirimi\Calendar\Controller;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\Calendar\Repository\Calendar\UbirimiCalendar;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;

class ListController extends UbirimiController
{
    public function indexAction(SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $session->set('selected_product_id', SystemProduct::SYS_PRODUCT_CALENDAR);
        $menuSelectedCategory = 'calendars';
        $calendars = $this->getRepository(UbirimiCalendar::class)->getByUserId($session->get('user/id'));

        $month = date('n');
        $year = date('Y');

        $sectionPageTitle = $session->get('client/settings/title_name') . ' / ' . SystemProduct::SYS_PRODUCT_CALENDAR_NAME . ' / My Calendar';

        return $this->render(__DIR__ . '/../Resources/views/List.php', get_defined_vars());
    }
}