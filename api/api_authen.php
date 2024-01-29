<?php 
#<!-- This is protected route. Accessed by only loggged in users -->
include("./config/db_connect.php");
include_once '../vendor/autoload.php';

use \Firebase\JWT\JWT;

include_once 'config/cors.php';
// header('Access-Control-Allow-Origin: *');
// header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
// header("Access-Control-Allow-Headers: Content-Type, Authorization, Access-Control-Allow-Headers, X-Requested-With");
    

// $headers = apache_request_headers();
// $authHeader = $headers['Authorization'];
// get request headers
$authHeader = getallheaders();

$output = array(
    "status" => "error"
);

if (isset($authHeader['Authorization']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $authHeader['Authorization'];
    $token = explode(" ", $token)[1];

    try {
        $key = "YOUR_SECRET_KEY";
        $decoded = JWT::decode($token, $key, array('HS256'));

        // Do some actions if token decoded successfully.

        // But for this demo let return decoded data
        http_response_code(200);
        $output['status'] = "success";
        $output['decoded'] = $decoded;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(array('message' => 'jwt expired'));
        $output['message'] = "jwt expired";
    }
} else {
    http_response_code(405);
    $output['message'] = "REQUEST_METHOD Error";
    // $output['message'] = "Please authenticate";
}
echo json_encode($output);
$conn->close();
?>