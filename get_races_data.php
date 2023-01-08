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

    $sql = "SELECT r.race as race, r.name as name, r.intro as intro, r.description as description, r.age as age, r.size as size, r.speed_walk as speed_walk, r.speed_climb as speed_climb, r.speed_burrow as speed_burrow, r.speed_swim as speed_swim, r.speed_fly as speed_fly, r.speed_hover as speed_hover, GROUP_CONCAT(s.subrace) as subraces,GROUP_CONCAT(s.name) as subrace_name FROM  `dnd5e_races` as r LEFT JOIN `dnd5e_subraces` as s ON r.race = s.parent_race WHERE r.race = '$race' GROUP BY s.parent_race ORDER BY r.race";
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
        $temp['intro'] = (isset($data['intro'])) ? $data['intro'] : '';
        $temp['description'] = (isset($data['description'])) ? split_section($data['description']) : [];

		// basic
        $temp_basic["age"] = (isset($data['age']) && $data['age'] != '') ? $data['age'] : '';
        $temp_basic["size"] = (isset($data['size']) && $data['size'] != '') ? $data['size'] : '';

        if(isset($data['speed_walk']) && $data['speed_walk'] != '') $temp_basic["speed"]["walk"] = $data['speed_walk'];
        if(isset($data['speed_climb']) && $data['speed_climb'] != '') $temp_basic["speed"]["climb"] = $data['speed_climb'];
        if(isset($data['speed_burrow']) && $data['speed_burrow'] != '') $temp_basic["speed"]["burrow"] = $data['speed_burrow'];
        if(isset($data['speed_swim']) && $data['speed_swim'] != '') $temp_basic["speed"]["swim"] = $data['speed_swim'];
        if(isset($data['speed_fly']) && $data['speed_fly'] != '') $temp_basic["speed"]["fly"] = $data['speed_fly'];
        if(isset($data['speed_hover']) && $data['speed_hover'] != '') $temp_basic["speed"]["hover"] = $data['speed_hover'];

        $temp['basic'] = $temp_basic;

		// race features	
		$race_features = array();	
		
		$race_features_sql = "SELECT * FROM `dnd5e_features` as cf WHERE type = 'race' AND apper_key = '$race' ORDER BY level ASC";
		$race_features_result = mysqli_query($db_connect,$race_features_sql);
		
		if ($race_features_result) 
		{
			if (mysqli_num_rows($race_features_result)>0) 
			{
				while ($race_features_row = mysqli_fetch_assoc($race_features_result)) 
				{
					$race_features[] = $race_features_row;
				}
			}
			mysqli_free_result($race_features_result);
		}
		
		if(count($race_features) > 0)
		{
			foreach($race_features as $race_feature)
			{
				$temp_feature = array();
				$temp_feature['fid'] = $race_feature['id'];
				$temp_feature['title'] = (isset($race_feature['name']) && $race_feature['name'] != '') ? $race_feature['name'] : '';
				$temp_feature['description'] = (isset($race_feature['description']) && $race_feature['description'] != '') ? split_section($race_feature['description']) : [];
				$temp_feature['replace_fid'] = (isset($race_feature['replace_fid']) && $race_feature['replace_fid'] != '') ? explode('|', $race_feature['replace_fid']) : [];

				if(isset($race_feature['dc_basic']) && $race_feature['dc_basic'] > 0)
				{
					$temp_feature['dc']['basic'] = $race_feature['dc_basic'];
					$temp_feature['dc']['ability_mod'] = $race_feature['dc_ability_mod'];
					$temp_feature['dc']['need_pb'] = ($race_feature['dc_need_pb'] == 'Y') ? $race_feature['dc_need_pb'] : 'N';
				}

                // race feature sublist
                if(isset($race_feature['sublist_choices']) && $race_feature['sublist_choices'] != '' && isset($race_feature['sublist_choice_num']) && $race_feature['sublist_choice_num'] != '')
                {
                    $sublist_choices = json_decode($race_feature['sublist_choices']);

                    foreach($sublist_choices as $sublist_choice)
                    {
                        $choice = json_decode(json_encode($sublist_choice), true);
                        $choice['subdesc'] = split_section($choice['subdesc']);

                        $temp_feature['sublist'][] = $choice;
                    }

                    $temp_feature['sublist_num'] = $race_feature['sublist_choice_num'];
                }
				
				$temp_race_features['featureitems'][] = $temp_feature;
			}
		}
	
        $temp['features'] = $temp_race_features;
        
        if(isset($data['subraces']) && $data['subraces'] != '' && $data['subraces'] != null && isset($data['subraces_name']) && $data['subraces_name'] != '' && $data['subraces_name'] != null)
		{
            $subraces = explode(',', $data['subraces']);
            $subraces_name = explode(',', $data['subraces_name']);
            if(count($subraces) > 0 && count($subraces) == count($subraces_name))
            {
                foreach($subraces as $index => $subrace)
                {
                    $temp_subrace_item = array();
                    $temp_subrace_item['title'] = $subraces_name[$index];
                    $temp_subrace_item['features'] = array();
    
                    $temp_subraces[$subrace] = $temp_subrace_item;
                }

                $subraces_features = array();	
            
                $subraces_features_sql = "SELECT * FROM `dnd5e_features` WHERE type = 'subrace' AND apper_key in ('".implode("', '", $subraces)."') ORDER BY apper_key ASC, level ASC";
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
                            
                            $temp_subraces[$subrace]['features']['featureitems'][] = $temp_feature;
                        }
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