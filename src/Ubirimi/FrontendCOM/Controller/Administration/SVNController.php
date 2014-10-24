<?php

namespace Ubirimi\FrontendCOM\Controller\Administration;

use Ubirimi\SvnHosting\Repository\SvnRepository;
use Ubirimi\UbirimiController;
use Ubirimi\Util;

class SVNController extends UbirimiController
{
    public function indexAction()
    {
        Util::checkSuperUserIsLoggedIn();

        $svnRepositories = SvnRepository::getAll(array('sort_by' => 'svn_repository.date_created', 'sort_order' => 'desc'));

        $selectedOption = 'svns';

        return $this->render(__DIR__ . '/../../Resources/views/administration/SVN.php', get_defined_vars());
    }
}
