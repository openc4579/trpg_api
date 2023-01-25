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
        /*
    case 'classeslist':
        $functionName = 'getClassesList';
        break;
        */
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

/*
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
*/

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
		$temp_level_features = array();
		$temp_subclasses = array();
		
        $temp['background'] = (isset($data['background'])) ? $data['background'] : '';
        $temp['name'] = (isset($data['name'])) ? $data['name'] : '';
        $temp['description'] = (isset($data['description'])) ? split_section($data['description']) : [];
        $temp['skill'] = (isset($data['skill'])) ? json_decode($data['skill'], true) : array();
        $temp['language'] = (isset($data['language'])) ? json_decode($data['language'], true) : array();
        $temp['tool'] = (isset($data['tool'])) ? json_decode($data['tool'], true) : array();
        $temp['item'] = (isset($data['item'])) ? split_section($data['item']) : [];
        $temp["feature"] = (isset($data['feature']) && $data['feature'] != '') ? explode('|', $data['feature']) : array();

        $return = $temp;
    }

    return $return;
}
?>