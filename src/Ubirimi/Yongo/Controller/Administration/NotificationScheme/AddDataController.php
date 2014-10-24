<?php

namespace Ubirimi\Yongo\Controller\Administration\NotificationScheme;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Ubirimi\Repository\General\UbirimiLog;
use Ubirimi\Repository\User\UbirimiGroup;
use Ubirimi\Repository\User\UbirimiUser;
use Ubirimi\SystemProduct;
use Ubirimi\UbirimiController;
use Ubirimi\Util;
use Ubirimi\Yongo\Repository\Field\Field;
use Ubirimi\Yongo\Repository\Issue\IssueEvent;
use Ubirimi\Yongo\Repository\Notification\Notification;
use Ubirimi\Yongo\Repository\Notification\NotificationScheme;


class AddDataController extends UbirimiController
{
    public function indexAction(Request $request, SessionInterface $session)
    {
        Util::checkUserIsLoggedInAndRedirect();

        $menuSelectedCategory = 'issue';

        $notificationSchemeId = $request->get('not_scheme_id');
        $eventId = $request->get('id');

        $notificationScheme = $this->getRepository(NotificationScheme::class)->getMetaDataById($notificationSchemeId);

        $events = IssueEvent::getByClient($session->get('client/id'));

        $users = $this->getRepository(UbirimiUser::class)->getByClientId($session->get('client/id'));
        $groups = $this->getRepository(UbirimiGroup::class)->getByClientIdAndProductId($session->get('client/id'), SystemProduct::SYS_PRODUCT_YONGO);
        $roles = $this->getRepository('yongo.permission.role')->getByClient($session->get('client/id'));

        $fieldsUserPickerMultipleSelection = $this->getRepository(Field::class)->getByClientIdAndFieldTypeId($session->get('client/id'), Field::CUSTOM_FIELD_TYPE_USER_PICKER_MULTIPLE_USER_CODE_ID);

        if ($request->request->has('confirm_new_data')) {

            $eventIds = $request->request->get('event');
            $notificationType = $request->request->get('type');

            $user = $request->request->get('user');
            $group = $request->request->get('group');
            $role = $request->request->get('role');
            $userPickerMultipleSelection = $request->request->get('select_user_picker_multiple_selection');

            $currentDate = Util::getServerCurrentDateTime();

            if ($notificationType) {

                for ($i = 0; $i < count($eventIds); $i++) {
                    // check for duplicate information
                    $duplication = false;

                    $dataNotification = $this->getRepository(NotificationScheme::class)->getDataByNotificationSchemeIdAndEventId($notificationSchemeId, $eventIds[$i]);

                    if ($dataNotification) {
                        while ($data = $dataNotification->fetch_array(MYSQLI_ASSOC)) {
                            if ($data['group_id'] && $data['group_id'] == $group) {
                                $duplication = true;
                            }
                            if ($data['user_id'] && $data['user_id'] == $user) {
                                $duplication = true;
                            }
                            if ($data['permission_role_id'] && $data['permission_role_id'] == $role) {
                                $duplication = true;
                            }
                            if ($notificationType == Notification::NOTIFICATION_TYPE_PROJECT_LEAD) {
                                if ($data['project_lead'])
                                    $duplication = true;
                            }
                            if ($notificationType == Notification::NOTIFICATION_TYPE_COMPONENT_LEAD) {
                                if ($data['component_lead'])
                                    $duplication = true;
                            }
                            if ($notificationType == Notification::NOTIFICATION_TYPE_CURRENT_ASSIGNEE) {
                                if ($data['current_assignee'])
                                    $duplication = true;
                            }
                            if ($notificationType == Notification::NOTIFICATION_TYPE_CURRENT_USER) {
                                if ($data['current_user'])
                                    $duplication = true;
                            }
                            if ($notificationType == Notification::NOTIFICATION_TYPE_REPORTER) {
                                if ($data['reporter'])
                                    $duplication = true;
                            }
                            if ($notificationType == Notification::NOTIFICATION_TYPE_ALL_WATCHERS) {
                                if ($data['all_watchers'])
                                    $duplication = true;
                            }
                            if ($notificationType == Notification::NOTIFICATION_TYPE_USER_PICKER_MULTIPLE_SELECTION) {
                                if ($data['custom_field_id'])
                                    $duplication = true;
                            }
                        }
                    }
                    if (!$duplication) {
                        $this->getRepository(NotificationScheme::class)->gaddData(
                            $notificationSchemeId,
                            $eventIds[$i],
                            $notificationType,
                            $user,
                            $group,
                            $role,
                            $userPickerMultipleSelection,
                            $currentDate
                        );

                        $this->getRepository(UbirimiLog::class)->add($session->get('client/id'), SystemProduct::SYS_PRODUCT_YONGO, $session->get('user/id'), 'ADD Yongo Notification Scheme Data', $currentDate);
                    }
                }
            }

            return new RedirectResponse('/yongo/administration/notification-scheme/edit/' . $notificationSchemeId);
        }

        $sectionPageTitle = $session->get('client/settings/title_name') . ' / ' . SystemProduct::SYS_PRODUCT_YONGO_NAME . ' / Create Notification Data';

        return $this->render(__DIR__ . '/../../../Resources/views/administration/notification_scheme/AddData.php', get_defined_vars());
    }
}