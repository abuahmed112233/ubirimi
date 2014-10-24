<?php

namespace Ubirimi\Documentador\Controller\Administration\Space;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\Documentador\Repository\Space\Space;
use Ubirimi\UbirimiController;
use Ubirimi\Util;

class PurgeAllDialogController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $spaceId = $request->get('id');

        $pages = $this->getRepository(Space::class)->getDeletedPages($spaceId);

        return new Response('This will remove all ' . $pages->num_rows . ' items permanently. Do you wish to continue?');
    }
}

