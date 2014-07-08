<?php

namespace Ubirimi\Agile\Controller\Sprint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\Agile\Repository\AgileSprint;
use Ubirimi\UbirimiController;
use Ubirimi\Util;

class StartController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $sprintId = $request->request->get('id');
        $startDate = $request->request->get('start_date');
        $endDate = $request->request->get('end_date');
        $name = $request->request->get('name');

        AgileSprint::start($sprintId, $startDate, $endDate, $name);

        return new Response('');
    }
}
