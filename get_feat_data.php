<?php
require_once 'db.php';
require_once 'functions.php';

$functionName = '';
$funcParam = '';
$type = (isset($_GET['type']) && $_GET['type'] != '') ? $_GET['type'] : '';
$id = (isset($_GET['id']) && $_GET['id'] != '') ? $_GET['id'] : '';

switch($type)
{
    case 'featfilter':
        $functionName = 'getFeatFilter';
        break;
    case 'featlist':
        $functionName = 'getFeatList';
        break;
    case 'feat':
        $functionName = 'getFeat';
        $funcParam = $id;
        break;
}

if($functionName != '')
{
    $return = call_user_func($functionName, $funcParam);
    echo json_encode($return);
}

function getFeatFilter()
{
    global $db_connect;

    $return = array();
    $ability_list = array();
    
    // ability
    $ability_sql = "SELECT a.ability as ability, a.name as name FROM `dnd5e_abilities` as a ORDER BY a.ability";
    $ability_result = mysqli_query($db_connect,$ability_sql);
    if ($ability_result) 
    {
        if (mysqli_num_rows($ability_result)>0) 
        {
            while ($row = mysqli_fetch_assoc($ability_result)) 
            {
                $ability = $row['ability'];
                $ability_list[$ability] = $row['name'];
            }
        }
        mysqli_free_result($ability_result);
    }

    $sql = "SELECT f.id as id, f.name as name, f.ability as ability, f.prerequisite as prerequisite FROM `dnd5e_features` as f WHERE f.type = 'feat'ORDER BY f.name";
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

    $ability = array();
    $ability['name'] = '屬性';
    if(count($ability_list) > 0)
    {
        foreach($ability_list as $key => $name)
        {
            $temp = array();
            $temp['key'] = $key;
            $temp['name'] = $name;

            $ability['option'][] = $temp;
        }
    }
    $return['choice']['ability'] = $ability;

    $prerequisite = array();
    $prerequisite['name'] = '先抉條件';

    $prerequisite['option'] = array(
        array('key'=>'Y', 'name'=>'有'),
        array('key'=>'N', 'name'=>'沒有')
    );

    $return['choice']['prerequisite'] = $prerequisite;

    return $return;
}

function getFeatList()
{
    global $db_connect;

    $return = array();
    $data = array();
    $ability_list = array();
    
    // ability
    $ability_sql = "SELECT a.ability as ability, a.name as name FROM `dnd5e_abilities` as a ORDER BY a.ability";
    $ability_result = mysqli_query($db_connect,$ability_sql);
    if ($ability_result) 
    {
        if (mysqli_num_rows($ability_result)>0) 
        {
            while ($row = mysqli_fetch_assoc($ability_result)) 
            {
                $ability = $row['ability'];
                $ability_list[$ability] = $row['name'];
            }
        }
        mysqli_free_result($ability_result);
    }

    $sql = "SELECT f.id as id, f.name as name, f.ability as ability, f.prerequisite as prerequisite FROM `dnd5e_features` as f WHERE f.type = 'feat'ORDER BY f.name";
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
		foreach($data as $feat_item)
		{
			$temp = array();
			
			$temp['key'] = (isset($feat_item['id'])) ? $feat_item['id'] : '';
			$temp['name'] = (isset($feat_item['name'])) ? $feat_item['name'] : '';
			$temp['ability'] = '';
			$temp['ability_name'] = '';
			$temp['prerequisite'] = (isset($feat_item['prerequisite'])) ? $feat_item['prerequisite'] : '';

            $ability = (isset($feat_item['ability']) && $feat_item['ability'] != '') ? json_decode($feat_item['ability'], true) : array();
            if(count($ability) > 0)
            {
                foreach($ability as $type => $ability_item)
                {
                    switch($type)
                    {
                        case isset($ability_list[$type]):
                            $temp['ability'] = $type;
                            $temp['ability_name'] .= $ability_list[$type].' +1。';
                            break;
                        case 'choose':
                            if(isset($ability_item['from']) && count($ability_item['from']) > 0 && isset($ability_item['amount']))
                            {
                                $temp['ability'] = implode(',',$ability_item['from']);
                                $temp['ability_name'] .= '選擇 ';
                                $temp['ability_name'] .= $ability_item['amount'].'個 ';
                                $isFirst = true;
                                foreach($ability_item['from'] as $index => $ability_code)
                                {
                                    if(isset($ability_list[$ability_code]))
                                    {
                                        $temp['ability_name'] .= ($isFirst) ? '':'或';
                                        $temp['ability_name'] .= $ability_list[$ability_code];
                                        $isFirst = false;
                                    }
                                }
                                $temp['ability_name'] .= ' +1。';
                            }
                            break;
                    }
                }
            }
			
			$return[] = $temp;
		}
    }

    return $return;
}

function getFeat($background)
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