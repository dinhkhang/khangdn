<?php

class VisitorNotificationView extends AppModel {

    public $useTable = 'visitor_notification_views';
    public $customSchema = array(
        'id' => '',
        'notification' => '',
        'visitor' => '',
        'count' => '',
        'created' => '',
        'modified' => '',
    );

    public function updateCount($visitorId = null, $notificationId = null) {
        if (isset($visitorId, $notificationId) && $visitorId instanceof MongoId && $notificationId instanceof MongoId) {
            $oldData = $this->find('first', ['conditions' => [
                    'id' => $visitorId,
                    'notification' => $notificationId,
            ]]);
            // if exists ? update else insert
            if ($oldData) {
                $oldData['VisitorNotificationView']['count'] += 1;
                return $this->save($oldData);
            } else {
                $data = ['VisitorNotificationView' => [
                        'notification' => $notificationId,
                        'visitor' => $visitorId,
                        'count' => 1
                ]];
                return $this->save($data);
            }
        }
    }

}
