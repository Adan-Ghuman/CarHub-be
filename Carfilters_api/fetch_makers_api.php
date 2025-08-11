<?php
header('content-Type: application/json');
header('Acess-control-Allow-Orign: *');
include '../config.php';

$query="SELECT id,maker_name FROM tbl_makers";

if (!empty($conn)) {    
    $result=mysqli_query($conn,$query) or die("tbl_Makers Query not working !");
}
if(mysqli_num_rows($result)>0)
{

    $data=mysqli_fetch_all($result,MYSQLI_ASSOC);
    echo json_encode($data);
}
else
{
        echo json_encode(array('message'=>'No  record found','status'=>false));
}
mysqli_close($conn);
?>