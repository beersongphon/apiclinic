<?php
	header('Access-Control-Allow-Origin: *');
	header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, Access-Control-Allow-Headers, X-Requested-With");
    //print_r(apache_request_headers());
    include("./config/db_connect.php");
    //exit();
    $headers = apache_request_headers();
    $auth = $headers['Authorization'];
	$sql = "SELECT * FROM user WHERE User_ID = '$auth'
	";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
        // output data of each row
		$row = $result->fetch_assoc();
		echo json_encode($row);
    } else {
        echo "0 results";
    }
	$conn->close();
?>
