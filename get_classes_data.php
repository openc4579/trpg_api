<?php
require_once 'db.php';

$functionName = '';
$funcParam = '';
$type = (isset($_GET['type']) && $_GET['type'] != '') ? $_GET['type'] : '';
$id = (isset($_GET['id']) && $_GET['id'] != '') ? $_GET['id'] : '';

switch($type)
{
    case 'classes':
        $functionName = 'getClasses';
        $funcParam = $id;
        break;
}

if($functionName != '')
{
    $return = call_user_func($functionName, $funcParam);
    echo json_encode($return);
}

function getClasses($class)
{
    global $db_connect;

    $return = array();
    $data = array();

    $sql = "SELECT * FROM `dnd5e_classes` WHERE class = '$class'";
    $result = mysqli_query($db_connect,$sql);
    if ($result) 
    {
        if (mysqli_num_rows($result)==1) 
        {
            while ($row = mysqli_fetch_assoc($result)) 
            {
                $data = $row;
            }
        }
        mysqli_free_result($result);
    }

    if(!empty($data))
    {
        $temp = array();
        $temp_basic = array();
		$temp_level_features = array();
		
        $temp['class'] = (isset($data['class'])) ? $data['class'] : '';
        $temp['name'] = (isset($data['name'])) ? $data['name'] : '';
        $temp['intro'] = (isset($data['intro'])) ? $data['intro'] : '';
        $temp['description'] = (isset($data['description'])) ? $data['description'] : '';

		// basic
        $temp_basic["hp"]["dice"] = "";
        $temp_basic["hp"]["stand"] = "";

        if(isset($data['hp_dice']) && $data['hp_dice'] != '')
        {
            $temp_basic["hp"]["dice"] = $data['hp_dice'];
            $temp_basic["hp"]["stand"] = ceil((1 + intval($data['hp_dice'])) / 2);
        }
        
        $temp_basic["prof"]["armor"] = (isset($data['prof_armor']) && $data['prof_armor'] != '') ? explode('|', $data['prof_armor']) : array();
        $temp_basic["prof"]["weapon"] = (isset($data['prof_weapon']) && $data['prof_weapon'] != '') ? explode('|', $data['prof_weapon']) : array();
        $temp_basic["prof"]["tool"] = (isset($data['prof_tool']) && $data['prof_tool'] != '') ? explode('|', $data['prof_tool']) : array();
        $temp_basic["prof"]["saving_throw"] = (isset($data['prof_saving_throw']) && $data['prof_saving_throw'] != '') ? explode('|', $data['prof_saving_throw']) : array();
        $temp_basic["prof"]["skill"]['choice'] = (isset($data['prof_skill']) && $data['prof_skill'] != '') ? explode('|', $data['prof_skill']) : array();
        $temp_basic["prof"]["skill"]['choice_num'] = (isset($data['prof_skill_choice_num']) && $data['prof_skill_choice_num'] != '') ? explode('|', $data['prof_skill_choice_num']) : array();

        if(isset($data['start_equipment']) && $data['start_equipment'] != '')
        {
            $choice = array();
            $choice_list = explode('|', $data['start_equipment']);
            if(count($choice_list) > 0)
            {
                foreach($choice_list as $choice_item)
                {
                    $choice_group = explode(',', $choice_item);
                    if(count($choice_group) == 2)
                    {
                        $twmp_choice = array();
                        $twmp_choice['a'] = $choice_group[0];
                        $twmp_choice['b'] = $choice_group[1];

                        $choice[] = $twmp_choice;
                    }
                }
            }
            $temp_basic["start_equipment"]["choice"] = $choice;
        }

        $temp_basic["start_equipment"]["start_gold"]["dice"] = (isset($data['start_gold_dice']) && $data['start_gold_dice'] != '') ? $data['start_gold_dice'] : '';
        $temp_basic["start_equipment"]["start_gold"]["dice_num"] = (isset($data['start_gold_dice_num']) && $data['start_gold_dice_num'] != '') ? $data['start_gold_dice_num'] : '';
        $temp_basic["start_equipment"]["start_gold"]["magn"] = (isset($data['start_gold_magn']) && $data['start_gold_magn'] != '') ? $data['start_gold_magn'] : '';

        $temp['basic'] = $temp_basic;

		// level features	
		$level_features = array();	
		
		$level_features_sql = "SELECT * FROM `dnd5e_classes_features` WHERE apper_class = '$class' ORDER BY level ASC";
		$level_features_result = mysqli_query($db_connect,$level_features_sql);
		
		if ($level_features_result) 
		{
			if (mysqli_num_rows($level_features_result)>0) 
			{
				while ($level_features_row = mysqli_fetch_assoc($level_features_result)) 
				{
					$level_features[] = $level_features_row;
				}
			}
			mysqli_free_result($level_features_result);
		}
		
		if(count($level_features) > 0)
		{
			foreach($level_features as $level_feature)
			{
				$level = $level_feature['level'];
				
				$temp_feature = array();
				$temp_feature['title'] = (isset($level_feature['name']) && $level_feature['name'] != '') ? $level_feature['name'] : '';
				$temp_feature['description'] = (isset($level_feature['description']) && $level_feature['description'] != '') ? $level_feature['description'] : '';
				
				if(isset($level_feature['dc_basic']) && $level_feature['dc_basic'] > 0)
				{
					$temp_feature['dc']['basic'] = $level_feature['dc_basic'];
					$temp_feature['dc']['ability_mod'] = $level_feature['dc_ability_mod'];
					$temp_feature['dc']['need_pb'] = ($level_feature['dc_need_pb'] == 'Y') ? $level_feature['dc_need_pb'] : 'N';
				}
				
				$temp_level_features[$level]['levelitems'][] = $temp_feature;
			}
		}
	
        $temp['levels'] = $temp_level_features;
		
        $return = $temp;
    }

    return $return;
}
?>