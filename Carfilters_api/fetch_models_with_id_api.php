<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include '../config.php';

// Decode JSON data from the client
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id'])) {
    $model_id = $data['id'];
    //echo "Model id :".$model_id;
    $query = "SELECT model_name FROM tbl_models WHERE id={$model_id}";

    $result = mysqli_query($conn, $query) or die("tbl_Models Query not working !");

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($data);
    } else {
        echo json_encode(array('message' => 'No record found', 'status' => false));
    }
} else {
    die(json_encode(array('message' => 'Model ID not provided in the request body', 'status' => false)));
}

mysqli_close($conn);
?>
