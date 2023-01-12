<?php
require_once 'db.php';
require_once 'functions.php';

$functionName = '';
$funcParam = '';
$type = (isset($_GET['type']) && $_GET['type'] != '') ? $_GET['type'] : '';
$id = (isset($_GET['id']) && $_GET['id'] != '') ? $_GET['id'] : '';

switch($type)
{
    case 'raceslist':
        $functionName = 'getRacesList';
        break;
    case 'races':
        $functionName = 'getRaces';
        $funcParam = $id;
        break;
}

if($functionName != '')
{
    $return = call_user_func($functionName, $funcParam);
    echo json_encode($return);
}

function getRacesList()
{
    global $db_connect;

    $return = array();
    $data = array();

    $sql = "SELECT r.race as race, r.name as name FROM `dnd5e_races` as r ORDER BY r.race";
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
		foreach($data as $race_item)
		{
			$temp = array();
			
			$temp['key'] = (isset($race_item['race'])) ? $race_item['race'] : '';
			$temp['name'] = (isset($race_item['name'])) ? $race_item['name'] : '';
			
			$return[] = $temp;
		}
    }

    return $return;
}

function getRaces($race)
{
    global $db_connect;

    $return = array();
    $data = array();

    $sql = "SELECT r.race as race, r.name as name, r.intro as intro, r.description as description, r.ability as ability, r.age as age, r.size as size, r.speed_walk as speed_walk, r.speed_climb as speed_climb, r.speed_burrow as speed_burrow, r.speed_swim as speed_swim, r.speed_fly as speed_fly, r.speed_hover as speed_hover, r.darkvision as darkvision, r.prof_skills as prof_skills, r.prof_weapon as prof_weapon, GROUP_CONCAT(s.subrace) as subraces FROM  `dnd5e_races` as r LEFT JOIN `dnd5e_subraces` as s ON r.race = s.parent_race WHERE r.race = '$race' GROUP BY s.parent_race ORDER BY r.race";
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
		$temp_race_features = array();
		$temp_subraces = array();
		
        $temp['race'] = (isset($data['race'])) ? $data['race'] : '';
        $temp['name'] = (isset($data['name'])) ? $data['name'] : '';
        $temp['intro'] = (isset($data['intro'])) ? split_section($data['intro']) : [];
        $temp['description'] = (isset($data['description'])) ? split_section($data['description']) : [];

		// basic
        $temp_basic["ability"] = array();
        $temp_basic["ability"]['choose'] = array("from" => array("str", "dex", "con", "int", "wis", "cha"), "num" => 3);
        if(isset($data['ability']) && $data['ability'] != '') $temp_basic["ability"] = array_merge($temp_basic["ability"], json_decode($data['ability'], true)) ;
        $temp_basic["age"] = (isset($data['age']) && $data['age'] != '') ? $data['age'] : '';
        $temp_basic["size"] = (isset($data['size']) && $data['size'] != '') ? $data['size'] : '';

		$temp_basic["speed"] = array();
        if(isset($data['speed_walk']) && $data['speed_walk'] != '') $temp_basic["speed"]["walk"] = $data['speed_walk'];
        if(isset($data['speed_climb']) && $data['speed_climb'] != '') $temp_basic["speed"]["climb"] = $data['speed_climb'];
        if(isset($data['speed_burrow']) && $data['speed_burrow'] != '') $temp_basic["speed"]["burrow"] = $data['speed_burrow'];
        if(isset($data['speed_swim']) && $data['speed_swim'] != '') $temp_basic["speed"]["swim"] = $data['speed_swim'];
        if(isset($data['speed_fly']) && $data['speed_fly'] != '') $temp_basic["speed"]["fly"] = $data['speed_fly'];
        if(isset($data['speed_hover']) && $data['speed_hover'] != '') $temp_basic["speed"]["hover"] = $data['speed_hover'];
		
        $temp_basic["darkvision"] = (isset($data['darkvision']) && $data['darkvision'] != '') ? $data['darkvision'] : '';

		$temp_basic["prof"] = array();
        if(isset($data['prof_skills']) && $data['prof_skills'] != '') $temp_basic["prof"]["skills"] = $data['prof_skills'];
        if(isset($data['prof_weapon']) && $data['prof_weapon'] != '') $temp_basic["prof"]["weapon"] = $data['prof_weapon'];

		$temp_basic["other"] = array();
        if(isset($data['resistance']) && $data['resistance'] != '') $temp_basic["other"]["resistance"] = $data['resistance'];
        if(isset($data['immune']) && $data['immune'] != '') $temp_basic["speed"]["immune"] = $data['immune'];
        if(isset($data['immune_condition']) && $data['immune_condition'] != '') $temp_basic["speed"]["immune_condition"] = $data['immune_condition'];

        $temp['basic'] = $temp_basic;

		// level features	
		$level_features = array();	
		
		$level_features_sql = "SELECT * FROM `dnd5e_features` as cf WHERE type = 'race' AND apper_key = '$race' ORDER BY level ASC";
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
				
				$temp_race_features[$level]['featureitems'][] = $temp_feature;
			}
		}
	
        $temp['features'] = $temp_race_features;
		
		// subrace
        $subraces_list = explode(',', $data['subraces']);
      
        if(count($subraces_list) > 0)
		{
			$subraces = array();	
		
			$subraces_sql = "SELECT * FROM `dnd5e_subraces` as cf WHERE subrace in ('".implode("', '", $subraces_list)."') ORDER BY subrace";
			$subraces_result = mysqli_query($db_connect,$subraces_sql);
		
			if ($subraces_result) 
			{
				if (mysqli_num_rows($subraces_result)>0) 
				{
					while ($subraces_result_row = mysqli_fetch_assoc($subraces_result)) 
					{
						$subraces[] = $subraces_result_row;
					}
				}
				mysqli_free_result($subraces_result);
			}
		
			if(count($subraces) > 0)
			{
				foreach($subraces as $subrace_item)
				{
					$temp_subrace_item = array();
					
					$subrace = $subrace_item['subrace'];
					
					$temp_subrace_item['title'] = $subrace_item['name'];
					$temp_subrace_item['description'] = $subrace_item['description'];

					$temp_subrace_basic = array();
					if($subrace_item['speed'] != null) $temp_subrace_basic['speed'] = json_decode($subrace_item['speed'], true);
					if($subrace_item['darkvision'] != null) $temp_subrace_basic['darkvision'] = json_decode($subrace_item['darkvision'], true);
					if($subrace_item['prof'] != null) $temp_subrace_basic['prof'] = json_decode($subrace_item['prof'], true);
					if($subrace_item['ability'] != null) $temp_subrace_basic['ability'] = json_decode($subrace_item['ability'], true);

					$temp_subrace_item['basic'] = $temp_subrace_basic ;

					$temp_subrace_item['features'] = array();
    
                    $temp_subraces[$subrace] = $temp_subrace_item;
				}
			}

			$subraces_features = array();	
		
			$subraces_features_sql = "SELECT * FROM `dnd5e_features` WHERE type = 'subrace' AND apper_key in ('".implode("', '", $subraces_list)."') ORDER BY apper_key ASC, level ASC";
			$subraces_features_result = mysqli_query($db_connect,$subraces_features_sql);
			
			if ($subraces_features_result) 
			{
				if (mysqli_num_rows($subraces_features_result)>0) 
				{
					while ($subraces_features_row = mysqli_fetch_assoc($subraces_features_result)) 
					{
						$subraces_features[] = $subraces_features_row;
					}
				}
				mysqli_free_result($subraces_features_result);
			}

			if(count($subraces_features) > 0)
			{
				foreach($subraces_features as $subraces_feature)
				{
					$subrace = $subraces_feature['apper_key'];
					
					if(isset($temp_subraces[$subrace]['features']))
					{
						$level = $subraces_feature['level'];
					
						$temp_feature = array();
						$temp_feature['fid'] = $subraces_feature['id'];
						$temp_feature['title'] = (isset($subraces_feature['name']) && $subraces_feature['name'] != '') ? $subraces_feature['name'] : '';
						$temp_feature['description'] = (isset($subraces_feature['description']) && $subraces_feature['description'] != '') ? split_section($subraces_feature['description']) : [];
						$temp_feature['replace_fid'] = (isset($subraces_feature['replace_fid']) && $subraces_feature['replace_fid'] != '') ? explode('|', $subraces_feature['replace_fid']) : [];
						
						if(isset($subraces_feature['dc_basic']) && $subraces_feature['dc_basic'] > 0)
						{
							$temp_feature['dc']['basic'] = $subraces_feature['dc_basic'];
							$temp_feature['dc']['ability_mod'] = $subraces_feature['dc_ability_mod'];
							$temp_feature['dc']['need_pb'] = ($subraces_feature['dc_need_pb'] == 'Y') ? $subraces_feature['dc_need_pb'] : 'N';
						}

						// subrace feature sublist
						if(isset($subraces_feature['sublist_choices']) && $subraces_feature['sublist_choices'] != '' && isset($subraces_feature['sublist_choice_num']) && $subraces_feature['sublist_choice_num'] != '')
						{
							$sublist_choices = json_decode($subraces_feature['sublist_choices']);
		
							foreach($sublist_choices as $sublist_choice)
							{
								$choice = json_decode(json_encode($sublist_choice), true);
								$choice['subdesc'] = split_section($choice['subdesc']);
		
								$temp_feature['sublist'][] = $choice;
							}
		
							$temp_feature['sublist_num'] = $subraces_feature['sublist_choice_num'];
						}
						
						$temp_subraces[$subrace]['features'][$level]['featureitems'][] = $temp_feature;
					}
				}
            }
		}
        
        $temp['subraces'] = $temp_subraces;
		
        $return = $temp;
    }

    return $return;
}
?>