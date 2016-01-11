<?php

class QuestionGroup extends AppModel {

    public $useTable = 'question_groups';
    public $customSchema = array(
        'id' => null,
        'name' => '',
        'cp_code' => '',
        'cate_code' => '',
        'description' => '',
        'order' => 0,
        'status' => 0,
        'user' => null,
        'modified' => null,
        'questions' => null,
    );

    public function load($region) {
        $return = $optionsRegion = [];
        $optionsRegion['conditions']['name']['$regex'] = new MongoRegex("/" . mb_strtolower($region) . "/i");
        $result = $this->find('list', $optionsRegion);
        foreach ($result AS $key => $item) {
            $return[] = new MongoId($key);
            unset($item);
        }
        return $return;
    }

    public function getOne() {
        $query = [
        ];
        $arr_question_group = $this->find('first', $query);

        return $arr_question_group;
    }

    public function getById($id) {
        $query = [
            'conditions' => ["_id" => $id]
        ];
        $arr_question_group = $this->find('first', $query);

        return $arr_question_group;
    }

    public function getArrGroupPackageOthers($limit, $arr_id, $package) {

        $options = array(
            'limit' => $limit,
            'conditions' => array(
                'status' => 2,
            ),
        );
        if (!empty($arr_id)) {
            $options['conditions']['_id'] = ['$nin' => $arr_id];
        }

        $arr_group = $this->find('all', $options);
        $arr_group_package = [];
        $arr_group_id = [];

        if (!empty($arr_group)) {
            foreach ($arr_group as $v) {
                $group_id = new MongoId($v['QuestionGroup']['id']);
                $arr_group_id[] = $group_id;
                $arr_group_package[] = ['group_id' => $group_id, 'package' => $package];
            }
        }

        return ['arr_group_package' => $arr_group_package, 'arr_group_id' => $arr_group_id];
    }

    public function getArrGroupIdOthers($limit, $arr_id) {

        $options = array(
            'limit' => $limit,
            'conditions' => array(
                'status' => 2,
            ),
        );
        if (!empty($arr_id)) {
            $options['conditions']['_id'] = ['$nin' => $arr_id];
        }

        $arr_group = $this->find('all', $options);
        $arr_group_id = [];

        if (!empty($arr_group)) {

            foreach ($arr_group as $v) {

                $arr_group_id[] = new MongoId($v['QuestionGroup']['id']);
            }
        }

        return $arr_group_id;
    }

    public function splitArrGroupIdByPackage($arr_group_package, $package) {

        $arr_group_id = [];
        $arr_group_new = [];

        if (!empty($arr_group_package)) {
            foreach ($arr_group_package as $v) {
                if ($v['package'] == $package) {
                    $arr_group_id[] = $v;
                } else {
                    $arr_group_new[] = $v;
                }
            }
        }

        return ['arr_group_id' => $arr_group_id, 'arr_group_new' => $arr_group_new];
    }

    public function allocate($player, $package, $limit, $log_file_name = null, $time_current = null) {

        if (empty($log_file_name)) {

            $log_file_name = __CLASS__ . '_' . __FUNCTION__;
        }

        if (empty($time_current)) {

            $time_current = date('Y-m-d H:i:s');
        }

        // nếu bộ câu hỏi đã được cấp phát trước đó, thì không cấp phát lại nữa
        $package_alias = $this->getPackageAlias($package);
        if (
                !empty($package_alias) &&
                !empty($player['Player'][$package_alias]['time_send_question']) &&
                date('Ymd', $player['Player'][$package_alias]['time_send_question']->sec) >= date('Ymd', strtotime($time_current))
        ) {

            $this->logAnyFile(__('Can not allocate question group, because they were allocated for player due to phone=%s before', $player['Player']['phone']), $log_file_name);
            return array(
                'id' => $player['Player']['id'],
            );
        }

        $options = array(
            'limit' => $limit,
            'conditions' => array(
                'status' => 2,
            ),
        );
        $answered_groups = !empty($player['Player']['answered_groups']) ?
                $player['Player']['answered_groups'] : array();
        if (!empty($answered_groups)) {

            $options['conditions']['_id'] = array(
                '$nin' => $answered_groups,
            );
        }

        $get_question_group = $this->find('all', $options);
        if (empty($get_question_group)) {

            $this->logAnyFile(__('Allocate repeated question group for player due to phone=%s', $player['Player']['phone']), $log_file_name);
            $get_question_group = $this->find('all', array(
                'limit' => $limit,
                'conditions' => array(
                    'status' => 2,
                ),
            ));
        }

        if (empty($get_question_group)) {

            $this->logAnyFile(__('Can not allocate any question group for player due to phone=%s, because the resource is empty', $player['Player']['phone']), $log_file_name);
            return false;
        }
        $num_questions = !empty($player['Player']['num_questions']) ?
                $player['Player']['num_questions'] : array();
        $count_group_aday = !empty($player['Player']['count_group_aday']) ?
                $player['Player']['count_group_aday'] : 0;

        if ($package == 'G1' || $package == 'G7') {

            $count_group_aday = $count_group_aday + 3;
        } elseif ($package == 'MUA') {

            $count_group_aday = $count_group_aday + 1;
        }

        foreach ($get_question_group as $v) {

            $num_questions[] = array(
                'group_id' => new MongoId($v['QuestionGroup']['id']),
                'package' => $package,
            );
            $answered_groups[] = new MongoId($v['QuestionGroup']['id']);
        }

        $player_data = array(
            'id' => $player['Player']['id'],
            'num_questions' => $num_questions,
            'answered_groups' => $answered_groups,
            'count_group_aday' => $count_group_aday,
        );

        // trường hợp là gói MUA thì không update vào $package_alias . '.time_send_question'
        if (!empty($package_alias)) {

            $player_data[$package_alias] = $player['Player'][$package_alias];
            $player_data[$package_alias]['time_send_question'] = new MongoDate(strtotime($time_current));
        }

        return $player_data;
    }

}
