<?php

namespace Ubirimi\Calendar\Controller\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\Calendar\Repository\Calendar\UbirimiCalendar;
use Ubirimi\Repository\General\UbirimiClient;
use Ubirimi\UbirimiController;
use Ubirimi\Util;

class AddConfirmController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();
        $clientId = $session->get('client/id');
        $clientSettings = $this->getRepository(UbirimiClient::class)->getSettings($clientId);

        $calendars = $this->getRepository(UbirimiCalendar::class)->getByUserId($session->get('user/id'), 'array');
        $defaultDay = $request->get('day');
        $defaultMonth = $request->get('month');
        $defaultYear = $request->get('year');

        $firstCalendar = $calendars[0];
        if ($defaultDay < 10) {
            $defaultDay = '0' . $defaultDay;
        }
        if ($defaultMonth < 10) {
            $defaultMonth = '0' . $defaultMonth;
        }
        $defaultEventStartDate = $defaultYear . '-' . $defaultMonth . '-' . $defaultDay . ' 00:00';

        $startDate = new \DateTime($defaultYear . '-' . $defaultMonth . '-' . $defaultDay, new \DateTimeZone($clientSettings['timezone']));
        $dayInWeek = date_format($startDate, 'w');

        return $this->render(__DIR__ . '/../../Resources/views/event/AddConfirm.php', get_defined_vars());
    }
}