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

namespace Ubirimi\Agile\Controller\Sprint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Ubirimi\Agile\Repository\Board\Board;
use Ubirimi\Agile\Repository\Sprint\Sprint;
use Ubirimi\UbirimiController;
use Ubirimi\Util;


class CompleteController extends UbirimiController
{
    public function indexAction(Request $request)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $sprintId = $request->request->get('id');
        $boardId = $request->request->get('board_id');

        $lastColumn = $this->getRepository(Board::class)->getLastColumn($boardId);
        $completeStatuses = $this->getRepository(Board::class)->getColumnStatuses($lastColumn['id'], 'array', 'id');

        $this->getRepository(Board::class)->transferNotDoneIssues($boardId, $sprintId, $completeStatuses);

        $this->getRepository(Sprint::class)->complete($sprintId);

        return new Response('');
    }
}
