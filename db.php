<?php
require('config.php');
$db_connect = mysqli_connect($host,$db_user,$db_password,$db_name);
if($db_connect){
    mysqli_query($db_connect,'SET NAMES uff8');
    // echo "正確連接資料庫";
}
else {
    echo "不正確連接資料庫</br>" . mysqli_connect_error();
}
?>