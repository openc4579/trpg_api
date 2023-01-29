<?php
require_once 'db.php';
require_once 'functions.php';

$functionName = '';
$funcParam = '';
$type = (isset($_GET['type']) && $_GET['type'] != '') ? $_GET['type'] : '';
$id = (isset($_GET['id']) && $_GET['id'] != '') ? $_GET['id'] : '';

switch($type)
{
    case 'backgroundindex':
        $functionName = 'getBackgroundIndex';
        break;
    case 'backgroundlist':
        $functionName = 'getBackgroundList';
        break;
    case 'background':
        $functionName = 'getBackground';
        $funcParam = $id;
        break;
}

if($functionName != '')
{
    $return = call_user_func($functionName, $funcParam);
    echo json_encode($return);
}

function getBackgroundIndex()
{
    global $db_connect;

    $return = array();
    $data = array();

    $sql = "SELECT c.class as class, c.name as name, c.intro as intro FROM `dnd5e_classes` as c ORDER BY c.class";
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
			$temp['intro'] = (isset($class_item['intro'])) ? $class_item['intro'] : '';
			
			$return[] = $temp;
		}
    }

    return $return;
}

function getBackgroundList()
{
    global $db_connect;

    $return = array();
    $data = array();
    $skill_list = array();
    
    // skill
    $skill_sql = "SELECT s.skill as skill, s.name as name FROM `dnd5e_skills` as s ORDER BY s.skill";
    $skill_result = mysqli_query($db_connect,$skill_sql);
    if ($skill_result) 
    {
        if (mysqli_num_rows($skill_result)>0) 
        {
            while ($row = mysqli_fetch_assoc($skill_result)) 
            {
                $skill = $row['skill'];
                $skill_list[$skill] = $row['name'];
            }
        }
        mysqli_free_result($skill_result);
    }

    $sql = "SELECT b.background as background, b.name as name, b.skill as skill, b.feature as feature FROM `dnd5e_background` as b WHERE b.background != 'customize' ORDER BY b.background";
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
		foreach($data as $background_item)
		{
			$temp = array();
			
			$temp['key'] = (isset($background_item['background'])) ? $background_item['background'] : '';
			$temp['name'] = (isset($background_item['name'])) ? $background_item['name'] : '';
			$temp['skill'] = '';
			$temp['feats'] = '';

            if(isset($background_item['skill']) && $background_item['skill'] != '')
            {
                $skill_text_list = array();
                $skills = json_decode($background_item['skill'], true);

                if(count($skills) > 0)
                {
                    foreach($skills as $skill => $count)
                    {
                        if(isset($skill_list[$skill]))
                        {
                            $skill_text_list[] = $skill_list[$skill];
                        }
                    }
                }
                $temp['skill'] = implode('、', $skill_text_list);
            }

            if(isset($background_item['feature']))
            {
                $temp['feats'] = implode('、', explode('|', $background_item['feature']));
            }
			
			$return[] = $temp;
		}
    }

    return $return;
}

function getBackground($background)
{
    global $db_connect;

    $return = array();
    $data = array();

    $sql = "SELECT b.background as background, b.name as name, b.description as description, b.skill as skill, b.language as language, b.tool as tool, b.item as item, b.feature as feature FROM  `dnd5e_background` as b WHERE b.background = '$background' ORDER BY b.background";
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
		$temp_features = array();
		
        $temp['background'] = (isset($data['background'])) ? $data['background'] : '';
        $temp['name'] = (isset($data['name'])) ? $data['name'] : '';
        $temp['description'] = (isset($data['description'])) ? split_section($data['description']) : [];
        $temp['skill'] = (isset($data['skill'])) ? json_decode($data['skill'], true) : array();
        $temp['language'] = (isset($data['language'])) ? json_decode($data['language'], true) : array();
        $temp['tool'] = (isset($data['tool'])) ? json_decode($data['tool'], true) : array();
        $temp['item'] = (isset($data['item'])) ? split_section($data['item']) : [];
        $temp['feats'] = (isset($data['feature'])) ? explode('|', $data['feature']) : [];

        $temp["features"] = array();
        if(count($temp['feats']) > 0)
        {
            $feature_list = $temp['feats'];

            $features = array();

            $feature_sql = "SELECT * FROM `dnd5e_features` as f WHERE type = 'feat' AND name in ('".implode("','", $feature_list)."') ORDER BY level ASC";
            $feature_result = mysqli_query($db_connect,$feature_sql);

            if ($result) 
            {
                if (mysqli_num_rows($feature_result) > 0) 
                {
                    while ($row = mysqli_fetch_assoc($feature_result)) 
                    {
                        $features[] = $row;
                    }
                }
                mysqli_free_result($feature_result);
            }
		
            if(count($features) > 0)
            {
                foreach($features as $feature)
                {
                    $level = $feature['level'];
                    
                    $temp_feature = array();
                    $temp_feature['fid'] = $feature['id'];
                    $temp_feature['title'] = (isset($feature['name']) && $feature['name'] != '') ? $feature['name'] : '';
                    $temp_feature['description'] = (isset($feature['description']) && $feature['description'] != '') ? split_section($feature['description']) : [];
                    $temp_feature['replace_fid'] = (isset($feature['replace_fid']) && $feature['replace_fid'] != '') ? explode('|', $feature['replace_fid']) : [];
    
                    if(isset($feature['dc_basic']) && $feature['dc_basic'] > 0)
                    {
                        $temp_feature['dc']['basic'] = $feature['dc_basic'];
                        $temp_feature['dc']['ability_mod'] = $feature['dc_ability_mod'];
                        $temp_feature['dc']['need_pb'] = ($feature['dc_need_pb'] == 'Y') ? $feature['dc_need_pb'] : 'N';
                    }
    
                    // level feature sublist
                    if(isset($feature['sublist_choices']) && $feature['sublist_choices'] != '' && isset($feature['sublist_choice_num']) && $feature['sublist_choice_num'] != '')
                    {
                        $sublist_choices = json_decode($feature['sublist_choices']);
    
                        foreach($sublist_choices as $sublist_choice)
                        {
                            $choice = json_decode(json_encode($sublist_choice), true);
                            $choice['subdesc'] = split_section($choice['subdesc']);
    
                            $temp_feature['sublist'][] = $choice;
                        }
    
                        $temp_feature['sublist_num'] = $feature['sublist_choice_num'];
                    }
                    
                    $temp_features[$level]['featureitems'][] = $temp_feature;
                }
            }
        
            $temp['features'] = $temp_features;
        }

        $return = $temp;
    }

    return $return;
}
?>