<?php
include_once 'config/dbh.php';
include_once '../vendor/autoload.php';

use \Firebase\JWT\JWT;

include_once 'config/cors.php';

$output = array(
    "status" => "error"
);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = file_get_contents('php://input');
    $request = json_decode($content, true);

	// $Username = $request->user_username;
    // $Password = $request->user_password;
    $Username = $request['user_username'];
    $Password = $request['user_password'];
    // $Password = md5($request["password"]);
    $sql = "SELECT * FROM tb_user WHERE user_username = '$Username'";
    $result = $conn->query($sql); 
    if (isset($result) && ($result->num_rows > 0)) {
        $row = $result->fetch_assoc();
        if (password_verify($Password, $row['user_password'])) {
            $key = "YOUR_SECRET_KEY";  // JWT KEY
            $payload = array(
			    // 'user_id' => $row['id'],
			    'user_username' => $row['user_username'],
			    // 'firstname' => $row['firstname'],
			    // 'lastname' => $row['lastname']
            );
            $token = JWT::encode($payload, $key);
            http_response_code(200);
            $output['status'] = "success";
            $output['message'] = "Welcome " . $row['user_username'];
            $output['data'] = $payload;
            $output['token'] = $token;
        } else {
            http_response_code(400);
            $output['message'] = "Invalid email or password";
        }
    } else {
        http_response_code(400);
        $output['message'] = "Login Failed!";
    }
} else {
    http_response_code(401);
    $output['message'] = "REQUEST_METHOD Error";
}
echo json_encode($output);
$conn->close();
