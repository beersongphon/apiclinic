<?php
include("./config/db_connect.php");
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
  $Username = $request['username'];
  $Password = $request['password'];
  // $Password = md5($request["password"]);
  $sql = "SELECT *, doctor.Department AS Doctor_Department, employee.Department AS Emp_Department FROM user 
    LEFT JOIN doctor ON doctor.Doctor_ID = user.User_ID 
    LEFT JOIN employee ON employee.Emp_ID = user.User_ID 
    WHERE user.Username = '$Username'";
  $result = $conn->query($sql);
  if (isset($result) && ($result->num_rows > 0)) {
    $row = $result->fetch_assoc();
    if (password_verify($Password, $row['Password'])) {
      $issued_at = time();
      $expiration_time = $issued_at + (60 * 60); // valid for 1 hour
      $key = "YOUR_SECRET_KEY";  // JWT KEY
      if ($row['Doctor_Department'] ) {
        $department = $row['Doctor_Department'];
      } else if ($row['Emp_Department'] ) {
        $department = $row['Emp_Department'];
      }
      $payload = array(
        // 'user_id' => $row['id'],
        'Username' => $row['Username'],
        'Department' => $department,
        'iat' => $issued_at,
        'exp' => $expiration_time
      );
      $token = JWT::encode($payload, $key);
      http_response_code(200);
      $output['status'] = "success";
      $output['message'] = "Welcome " . $row['Username'];
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
?>