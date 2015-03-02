<?php

/*
 *  Copyright (C) 2012-2014 SC Ubirimi SRL <info-copyright@ubirimi.com>
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

namespace Ubirimi\Yongo\Controller\Administration\Project\Version;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;
use Ubirimi\Yongo\Repository\Project\YongoProject;


class EditController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $versionId = $request->get('id');
        $version = $this->getRepository(YongoProject::class)->getVersionById($versionId);
        $projectId = $version['project_id'];
        $project = $this->getRepository(YongoProject::class)->getById($projectId);

        if ($project['client_id'] != $session->get('client/id')) {
            return new RedirectResponse('/general-settings/bad-link-access-denied');
        }

        $emptyName = false;
        $alreadyExists = false;

        if ($request->request->has('edit_release')) {
            $name = Util::cleanRegularInputField($request->request->get('name'));
            $description = Util::cleanRegularInputField($request->request->get('description'));

            if (empty($name)) {
                $emptyName = true;
            }

            $releaseDuplicate = $this->getRepository(YongoProject::class)->getVersionByName(
                $projectId,
                $name,
                $versionId
            );
            if ($releaseDuplicate) {
                $alreadyExists = true;
            }

            if (!$emptyName && !$alreadyExists) {
                $currentDate = Util::getServerCurrentDateTime();
                $this->getRepository(YongoProject::class)->updateVersionById(
                    $versionId,
                    $name,
                    $description,
                    $currentDate
                );

                $this->getLogger()->addInfo('UPDATE Project Version ' . $name, $this->getLoggerContext());

                return new RedirectResponse('/yongo/administration/project/versions/' . $projectId);
            }
        }

        $menuSelectedCategory = 'project';
        $sectionPageTitle = $session->get(
                'client/settings/title_name'
            ) . ' / ' . SystemProduct::SYS_PRODUCT_YONGO_NAME . ' / Update Project Version';

        return $this->render(
            __DIR__ . '/../../../../Resources/views/administration/project/version/Edit.php',
            get_defined_vars()
        );
    }
}
