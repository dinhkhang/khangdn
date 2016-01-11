<?php

class NotificationsController extends AppController {

        public $uses = ['Notification', 'VisitorNotificationView'];
        public $debug_mode = 3;
        public $components = array(
            'TrackingLogCommon',
        );

        public function index() {

                $this->setInit();
//                $this->TrackingLogCommon->logAction('access', 'notification_list');

                $limit = (int) trim($this->request->query('limit'));
                if ($limit <= 0) {

                        $limit = Configure::read('sysconfig.Home.search.limit');
                }
                $page = (int) trim($this->request->query('page'));
                if ($page <= 0) {

                        $page = 1;
                }
                $total = 0;

                // check token
                $tokenDecode = $this->validateToken();
                // get visitor id
                $visitorId = $tokenDecode->visitor->id;

                // xác định visitor_notification_groups
                $visitor_notification_groups = !empty($tokenDecode->Visitor['Visitor']['visitor_notification_groups']) ?
                        $tokenDecode->Visitor['Visitor']['visitor_notification_groups'] : array();

                // nếu không xác định thuộc nhóm thông báo nào
                if (empty($visitor_notification_groups)) {

                        $res = array(
                            'status' => 'success',
                            'data' => null,
                        );
                        $this->resSuccess($res);
                }

                $data = $this->Notification->getVisitorNotification(
                        $visitor_notification_groups, $visitorId, $limit, $page
                );

                if ($data) {

                        $total = $this->Notification->getVisitorNotificationCount(
                                $visitor_notification_groups
                        );
                }

                // return result
                $this->resSuccess(array(
                    'status' => 'success',
                    'data' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => $total,
                        'arr_notification' => $data
                    ]
                ));
        }

        public function info() {
                $this->setInit();
//                $this->TrackingLogCommon->logAction('access', 'notification_info');
                // check token
                $visitorId = $this->validateToken()->visitor->id;
                if (isset($this->request->query['notification_id'])) {
                        $data = $this->Notification->find('first', ['conditions' => [
                                'id' => new MongoId($this->request->query['notification_id']),
                                'status' => Configure::read('sysconfig.Common.STATUS_APPROVED'),
                        ]]);

                        if ($data) {
                                // update count visitor read notification
                                $this->VisitorNotificationView->updateCount(new MongoId($visitorId), new MongoId($this->request->query['notification_id']));
                                // return result
                                $this->resSuccess(array(
                                    'status' => 'success',
                                    'data' => ['arr_notification' => $data['Notification']]
                                ));
                        } else {
                                // error message notification id not exists
                                $this->resError('#not003');
                        }
                } else {
                        // error message notification id is missing
                        $this->resError('#not002');
                }
        }

}
