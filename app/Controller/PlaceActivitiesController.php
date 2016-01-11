<?php

class PlaceActivitiesController extends AppController {

    public $uses = array('PlaceActivity', 'Place');
    public $components = array('FileCommon');


    public function getbyplace() {  
        $this->layout = null;
        $this->autoRender = false;
        header('Content-Type: application/json');
        $URL_BASE_FILE_MANAGER =  Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');


        $place_id = $this->request->query("place_id") ; 

        $page = (int)$this->request->query('page');
        $limit = (int)$this->request->query('limit');
        if ($page < 1) {
            $page = 1;
        }
        if ($limit < 1) {
            $limit = 10;
        }
        $offset = ($page - 1) * $limit;

        $query_place_act = [];
        $query_place_act['limit'] = $limit;
        $query_place_act['offset'] = $offset;
        $query_place_act['order'] = array('order' => 'ASC');
        $query_place_act['fields'] = array('id', 'name', 'short_description', 'location', 'file_uris', 'object_icon', 'modified');
        $query_place_act['conditions'] = [ 
            "status" => 2,
            "place" => new MongoId($place_id)
            ];


        $total = $this->PlaceActivity->find('count', ['conditions' => [ 
            "status" => 2,
            "place" => new MongoId($place_id)
            ]]);

        $arr_place_act = $this->PlaceActivity->find('all', $query_place_act);
        $arr_place_act_result = [];
        if (!empty($arr_place_act))
        {
            foreach ($arr_place_act as $value) { 
                $place_act = array(
                    'id' => $value['PlaceActivity']["id"],
                    'name' => $value['PlaceActivity']['name'], 
                    'short_description' => $value['PlaceActivity']['short_description'], 
                    'icon' => "",//TODO
                    'banner' => "",
                    'logo' => "",
                );
                
                if (!empty($value['PlaceActivity']['file_uris']['banner']))
                {
                    $place_act['banner'] = $URL_BASE_FILE_MANAGER . array_values($value['PlaceActivity']['file_uris']['banner'])[0];
                }
                if (!empty($value['PlaceActivity']['file_uris']['logo']))
                {
                    $place_act['logo'] = $URL_BASE_FILE_MANAGER . array_values($value['PlaceActivity']['file_uris']['logo'])[0];
                }

                array_push($arr_place_act_result, $place_act); 
            } 
        }      

        $arr_result = ['arr_place_act' => $arr_place_act_result, 'page' => $page, 'limit' => $limit, 'total' => $total];

//            var_dump($arr_place);
        return json_encode($arr_result);
    }


    
    public function info() {  
        $this->layout = null;
        $this->autoRender = false;
        header('Content-Type: application/json');
        $URL_BASE_FILE_MANAGER =  Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');   
        $STATUS_APPROVED = Configure::read('sysconfig.Common.STATUS_APPROVED');        
        
        $arr_result = ["place_activity" => null];

        $lang_code = $this->request->query("lang_code") ;
        $user_id = $this->request->query("user_id") ;            
        $lat = $this->request->query("lat") ;
        $lng = $this->request->query("lng") ;
        $lang_code = "en"; 
        $id = $this->request->query("id") ; 
        //===================================== ACTIVITY INFO ===================================== 
              
        $query_activity = []; 
        $query_activity['fields'] = array('id', 'name', 'short_description', 'description', 'file_uris', 'modified'); 
        $query_activity['conditions'] = array(
            "status" => $STATUS_APPROVED,  
            "id" => new MongoId($id),  
            
            ); 
 
        $activity_val = $this->PlaceActivity->find('first', $query_activity); 
//        var_dump($activity_val);
        
        if (!empty($activity_val))
        {
            $activity_val = $activity_val["PlaceActivity"];

            $activity = array(
                'id' => $activity_val["id"],
                'name' => $activity_val['name'], 
                'short_description' => $activity_val["short_description"],  
                'description' => $this->convertAbsolutePathInContent($activity_val["description"]),   
                'icon' => "",//TODO
                'banner' => "",
                'logo' => "", 
                'favorite' => 0,
                'bookmark' => 0,
                'share' => "Hoạt động ngày hè thật vui",
                'modified' => date('H:i d/m/Y', $activity_val['modified']->sec)
            ); 


            if (!empty($activity_val["file_uris"]['banner'])) {
                $activity['banner'] = $URL_BASE_FILE_MANAGER . array_values($activity_val["file_uris"]['banner'])[0];
            }
            if (!empty($activity_val["file_uris"]['logo'])) {
                $activity['logo'] = $URL_BASE_FILE_MANAGER . array_values($activity_val["file_uris"]['logo'])[0];
            }
            $arr_result["place_activity"] = $activity;
 
        }
 
        return json_encode($arr_result);
    }  

}
