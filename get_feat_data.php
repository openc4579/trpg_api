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
			
			$temp['key'] = (isset($feat_item['name'])) ? $feat_item['name'] : '';
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
                                $isFirst = true;
                                foreach($ability_item['from'] as $index => $ability_code)
                                {
                                    if(isset($ability_list[$ability_code]))
                                    {
                                        $temp['ability_name'] .= ($isFirst) ? '':' 或 ';
                                        $temp['ability_name'] .= $ability_list[$ability_code];
                                        $isFirst = false;
                                    }
                                }
                                $temp['ability_name'] .= ' +1';
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

function getFeat($feat)
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

    $sql = "SELECT f.id as id, f.name as name, f.description as description, f.ability as ability, f.prerequisite as prerequisite FROM `dnd5e_features` as f WHERE f.type = 'feat' AND f.name = '$feat'";
    $result = mysqli_query($db_connect,$sql);
    if ($result) 
    {
        if (mysqli_num_rows($result) == 1) 
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
		$feat_item = $data[0];
        $temp = array();
        
        $temp['key'] = (isset($feat_item['name'])) ? $feat_item['name'] : '';
        $temp['name'] = (isset($feat_item['name'])) ? $feat_item['name'] : '';
        $temp['description'] = (isset($feat_item['description']) && $feat_item['description'] != '') ? split_section($feat_item['description']) : [];
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
                        $temp['ability_name'] .= $ability_list[$type].' 增加 1 點。上限為 20。';
                        break;
                    case 'choose':
                        if(isset($ability_item['from']) && count($ability_item['from']) > 0 && isset($ability_item['amount']))
                        {
                            $temp['ability'] = implode(',',$ability_item['from']);
                            $temp['ability_name'] .= '選擇 ';
                            $isFirst = true;
                            foreach($ability_item['from'] as $index => $ability_code)
                            {
                                if(isset($ability_list[$ability_code]))
                                {
                                    $temp['ability_name'] .= ($isFirst) ? '':' 或 ';
                                    $temp['ability_name'] .= $ability_list[$ability_code];
                                    $isFirst = false;
                                }
                            }
                            $temp['ability_name'] .= ' 增加 1 點。上限為 20。';
                        }
                        break;
                }
            }
		}
			
        $return = $temp;
    }

    return $return;
}
?>