<?php
require_once 'db.php';
require_once 'functions.php';

$functionName = '';
$funcParam = '';
$type = (isset($_GET['type']) && $_GET['type'] != '') ? $_GET['type'] : '';
$id = (isset($_GET['id']) && $_GET['id'] != '') ? $_GET['id'] : '';

switch($type)
{
    case 'classeslist':
        $functionName = 'getClassesList';
        break;
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

function getClassesList()
{
    global $db_connect;

    $return = array();
    $data = array();

    $sql = "SELECT c.class as class, c.name as name FROM `dnd5e_classes` as c ORDER BY c.class";
    $result = mysqli_query($db_connect,$sql);
    if ($result) 
    {
        if (mysqli_num_rows($result)>0) 
        {
            while ($row = mysqli_fetch_assoc($result)) 
            {
                $data[] = $row;
            }
        }
        mysqli_free_result($result);
    }

    if(count($data) > 0)
    {
		foreach($data as $class_item)
		{
			$temp = array();
			
			$temp['key'] = (isset($class_item['class'])) ? $class_item['class'] : '';
			$temp['name'] = (isset($class_item['name'])) ? $class_item['name'] : '';
			
			$return[] = $temp;
		}
    }

    return $return;
}

function getClasses($class)
{
    global $db_connect;

    $return = array();
    $data = array();

    $sql = "SELECT c.class as class, c.name as name, c.intro as intro, c.description as description, c.prof_bonus as prof_bonus, c.hp_dice as hp_dice, c.prof_armor as prof_armor, c.prof_weapon as prof_weapon, c.prof_tool as prof_tool, c.prof_saving_throw as prof_saving_throw, c.prof_skill as prof_skill, c.prof_skill_choice_num as prof_skill_choice_num, c.start_equipment as start_equipment, c.start_gold_dice as start_gold_dice, c.start_gold_dice_num as start_gold_dice_num, c.start_gold_magn as start_gold_magn, GROUP_CONCAT(s.subclass) as subclasses, GROUP_CONCAT(s.name) as subclasses_name  FROM  `dnd5e_classes` as c LEFT JOIN `dnd5e_subclasses` as s ON c.class = s.parent_class WHERE c.class = '$class' GROUP BY s.parent_class ORDER BY c.class";
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
		$temp_subclasses = array();
		
        $temp['class'] = (isset($data['class'])) ? $data['class'] : '';
        $temp['name'] = (isset($data['name'])) ? $data['name'] : '';
        $temp['intro'] = (isset($data['intro'])) ? $data['intro'] : '';
        $temp['description'] = (isset($data['description'])) ? split_section($data['description']) : [];
        $temp['prof_bonus'] = (isset($data['prof_bonus'])) ? explode('|', $data['prof_bonus']) : [];

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
					$twmp_choice = array();
					
                    if(count($choice_group) == 2)
                    {
                        $twmp_choice['a'] = $choice_group[0];
                        $twmp_choice['b'] = $choice_group[1];
                    }
					else
					{
                        $twmp_choice['a'] = $choice_group[0];
					}

                    $choice[] = $twmp_choice;
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
		
		$level_features_sql = "SELECT * FROM `dnd5e_features` as cf WHERE type = 'class' AND apper_key = '$class' ORDER BY level ASC";
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
				$temp_feature['fid'] = $level_feature['id'];
				$temp_feature['title'] = (isset($level_feature['name']) && $level_feature['name'] != '') ? $level_feature['name'] : '';
				$temp_feature['description'] = (isset($level_feature['description']) && $level_feature['description'] != '') ? split_section($level_feature['description']) : [];
				$temp_feature['replace_fid'] = (isset($level_feature['replace_fid']) && $level_feature['replace_fid'] != '') ? explode('|', $level_feature['replace_fid']) : [];

				if(isset($level_feature['dc_basic']) && $level_feature['dc_basic'] > 0)
				{
					$temp_feature['dc']['basic'] = $level_feature['dc_basic'];
					$temp_feature['dc']['ability_mod'] = $level_feature['dc_ability_mod'];
					$temp_feature['dc']['need_pb'] = ($level_feature['dc_need_pb'] == 'Y') ? $level_feature['dc_need_pb'] : 'N';
				}

                // level feature sublist
                if(isset($level_feature['sublist_choices']) && $level_feature['sublist_choices'] != '' && isset($level_feature['sublist_choice_num']) && $level_feature['sublist_choice_num'] != '')
                {
                    $sublist_choices = json_decode($level_feature['sublist_choices']);

                    foreach($sublist_choices as $sublist_choice)
                    {
                        $choice = json_decode(json_encode($sublist_choice), true);
                        $choice['subdesc'] = split_section($choice['subdesc']);

                        $temp_feature['sublist'][] = $choice;
                    }

                    $temp_feature['sublist_num'] = $level_feature['sublist_choice_num'];
                }
				
				$temp_level_features[$level]['featureitems'][] = $temp_feature;
			}
		}
	
        $temp['features'] = $temp_level_features;
        
        if(isset($data['subclasses']) && $data['subclasses'] != '' && $data['subclasses'] != null && isset($data['subclasses_name']) && $data['subclasses_name'] != '' && $data['subclasses_name'] != null)
		{
            $subclasses = explode(',', $data['subclasses']);
            $subclasses_name = explode(',', $data['subclasses_name']);
            if(count($subclasses) > 0 && count($subclasses) == count($subclasses_name))
            {
                foreach($subclasses as $index => $subclass)
                {
                    $temp_subclass_item = array();
                    $temp_subclass_item['title'] = $subclasses_name[$index];
                    $temp_subclass_item['features'] = array();
    
                    $temp_subclasses[$subclass] = $temp_subclass_item;
                }

                $subclasses_features = array();	
            
                $subclasses_features_sql = "SELECT * FROM `dnd5e_features` WHERE type = 'subclass' AND apper_key in ('".implode("', '", $subclasses)."') ORDER BY apper_key ASC, level ASC";
                $subclasses_features_result = mysqli_query($db_connect,$subclasses_features_sql);
                
                if ($subclasses_features_result) 
                {
                    if (mysqli_num_rows($subclasses_features_result)>0) 
                    {
                        while ($subclasses_features_row = mysqli_fetch_assoc($subclasses_features_result)) 
                        {
                            $subclasses_features[] = $subclasses_features_row;
                        }
                    }
                    mysqli_free_result($subclasses_features_result);
                }

                if(count($subclasses_features) > 0)
                {
                    foreach($subclasses_features as $subclasses_feature)
                    {
                        $subclass = $subclasses_feature['apper_key'];
                        
                        if(isset($temp_subclasses[$subclass]['features']))
                        {
                            $level = $subclasses_feature['level'];
                        
                            $temp_feature = array();
							$temp_feature['fid'] = $subclasses_feature['id'];
                            $temp_feature['title'] = (isset($subclasses_feature['name']) && $subclasses_feature['name'] != '') ? $subclasses_feature['name'] : '';
                            $temp_feature['description'] = (isset($subclasses_feature['description']) && $subclasses_feature['description'] != '') ? split_section($subclasses_feature['description']) : [];
                            $temp_feature['replace_fid'] = (isset($subclasses_feature['replace_fid']) && $subclasses_feature['replace_fid'] != '') ? explode('|', $subclasses_feature['replace_fid']) : [];
                            
                            if(isset($subclasses_feature['dc_basic']) && $subclasses_feature['dc_basic'] > 0)
                            {
                                $temp_feature['dc']['basic'] = $subclasses_feature['dc_basic'];
                                $temp_feature['dc']['ability_mod'] = $subclasses_feature['dc_ability_mod'];
                                $temp_feature['dc']['need_pb'] = ($subclasses_feature['dc_need_pb'] == 'Y') ? $subclasses_feature['dc_need_pb'] : 'N';
                            }

                            // subclass feature sublist
                            if(isset($subclasses_feature['sublist_choices']) && $subclasses_feature['sublist_choices'] != '' && isset($subclasses_feature['sublist_choice_num']) && $subclasses_feature['sublist_choice_num'] != '')
                            {
                                $sublist_choices = json_decode($subclasses_feature['sublist_choices']);
            
                                foreach($sublist_choices as $sublist_choice)
                                {
                                    $choice = json_decode(json_encode($sublist_choice), true);
                                    $choice['subdesc'] = split_section($choice['subdesc']);
            
                                    $temp_feature['sublist'][] = $choice;
                                }
            
                                $temp_feature['sublist_num'] = $subclasses_feature['sublist_choice_num'];
                            }
                            
                            $temp_subclasses[$subclass]['features'][$level]['featureitems'][] = $temp_feature;
                        }
                    }
                }
            }
		}
        
        $temp['subclasses'] = $temp_subclasses;
		
        $return = $temp;
    }

    return $return;
}
?>