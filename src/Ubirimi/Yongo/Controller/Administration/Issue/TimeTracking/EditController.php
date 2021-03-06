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

namespace Ubirimi\Yongo\Controller\Administration\Issue\TimeTracking;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\Repository\General\UbirimiClient;
use Ubirimi\UbirimiController;
use Ubirimi\Util;

class EditController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $menuSelectedCategory = 'system';
        $timeTrackingFlag = $session->get('yongo/settings/time_tracking_flag');
        $defaultTimeTracking = null;

        switch ($session->get('yongo/settings/time_tracking_default_unit')) {
            case 'w':
                $defaultTimeTracking = 'week';
                break;
            case 'd':
                $defaultTimeTracking = 'day';
                break;
            case 'h':
                $defaultTimeTracking = 'hours';
                break;
            case 'm':
                $defaultTimeTracking = 'minute';
                break;
        }

        if ($request->request->has('edit_time_tracking')) {
            $hoursPerDay = $request->request->get('hours_per_day');
            $daysPerWeek = $request->request->get('days_per_week');
            $defaultUnit = $request->request->get('default_unit');

            $this->getRepository(UbirimiClient::class)->updateTimeTrackingSettings(
                $session->get('client/id'),
                $hoursPerDay,
                $daysPerWeek,
                $defaultUnit
            );

            $this->getLogger()->addInfo('UPDATE Yongo Time Tracking Settings', $this->getLoggerContext());

            $session->set('yongo/settings/time_tracking_hours_per_day', $hoursPerDay);
            $session->set('yongo/settings/time_tracking_days_per_week', $daysPerWeek);
            $session->set('yongo/settings/time_tracking_default_unit', $defaultUnit);

            return new RedirectResponse('/yongo/administration/issue-features/time-tracking');
        }

        return $this->render(__DIR__ . '/../../../../Resources/views/administration/issue/time_tracking/Edit.php', get_defined_vars());
    }
}
