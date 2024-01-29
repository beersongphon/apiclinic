<?php
// header('Access-Control-Allow-Origin: *');
// header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
// header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With or other");
// header('Access-Control-Allow-Credentials: true');
// header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
header("Content-Type: application/json");
//Please create users database inside phpmysql admin and create userdetails tabel and create id, email and username fields
include("./config/db_connect.php");

$output = array(
    "status" => "error"
);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = file_get_contents('php://input');
    $request = json_decode($content, true);
    //Add user
    if (isset($request['myUserName'])) {
        $User_ID = $request['myUserID'];
        $User_Name = $request['myUserName'];
        $Address = $request['myAddress'];
        $Tel = $request['myTel'];
        $Salary = $request['mySalary'];
        $Department = $request['myDepartment'];
        $Username = $request['myUsername'];
        $Password = $request['myPassword'];
        // $Password = md5($request["myPassword"]);
        $hash = password_hash($Password, PASSWORD_DEFAULT);

        $sql = "SELECT * FROM user WHERE User_Name = '$User_Name'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            http_response_code(201);
            $output['message'] = "ไม่สามารถเพิ่มข้อมูลได้ เนื่องจากบัญชีนี้มีชื่อแล้ว";
        } else {
            $sql = "INSERT INTO user (User_ID, User_Name, Username, Password) VALUES ('$User_ID', '$User_Name', '$Username', '$hash')";
            $result = $conn->query($sql);
            if ($result) {
                if ($Department == "แพทย์") {
                    $sql = "INSERT INTO doctor (Doctor_ID, Doctor_Name, Address, Tel, Salary, Department) VALUES ('$User_ID', '$User_Name', '$Address', '$Tel', '$Salary', '$Department')";
                    $result = $conn->query($sql);
                    if ($result) {
                        $output['status'] = "success";
                        $output['message'] = "บันทึกข้อมูลสำเร็จ";
                        http_response_code(200);
                    } else {
                        $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: '$conn->error'";
                    }
                } elseif ($Department == "พนักงาน") {
                    $sql = "INSERT INTO employee (Emp_ID, Emp_Name, Address, Tel, Salary, Department) VALUES ('$User_ID', '$User_Name', '$Address', '$Tel', '$Salary', '$Department')";
                    $result = $conn->query($sql);
                    if ($result) {
                        http_response_code(201);
                        $output['status'] = "success";
                        $output['message'] = "บันทึกข้อมูลสำเร็จ";
                    } else {
                        $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: '$conn->error'";
                    }
                }
                $output['status'] = "success";
                $output['message'] = "บันทึกข้อมูลสำเร็จ";
            } else {
                $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: '$conn->error'";
            }
        }
        // http_response_code(500);
        // $output['message'] = "Internal Server error";
    }
    //Delete user
    elseif (isset($request['deleteid'])) {
        $Patient_ID = $request['deleteid'];

        $sql = "DELETE FROM card_patient WHERE Patient_ID = '$Patient_ID'";
        $result = $conn->query($sql);
        if ($result) {
            $sql = "DELETE FROM patient WHERE Patient_ID = '$Patient_ID'";
            $result = $conn->query($sql);
            if ($result) {
                $output['status'] = "success";
                $output['message'] = "บันทึกข้อมูลสำเร็จ";
            } else {
                $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: '$conn->error'";
            }
            $output['status'] = "success";
            $output['message'] = "บันทึกข้อมูลสำเร็จ";
        } else {
            $output['message'] = "Error deleting record: " . mysqli_error($conn);
        }
    }
    //Get single user details
    elseif (isset($request['patientid'])) {
        $sql = "SELECT *, 
        SUBSTRING_INDEX(user.User_Name, ' ', 1) AS Firstname, 
        SUBSTRING_INDEX(user.User_Name, ' ', -1) AS Lastname 
        FROM user
        LEFT JOIN doctor ON doctor.Doctor_ID = user.User_ID
        LEFT JOIN employee ON employee.Emp_ID = user.User_ID";

        $User_ID = $request['patientid'];

        if (strlen($User_ID) > 0) {
            $sql .= " WHERE user.User_ID = '$User_ID'";
        }
        $result = $conn->query($sql);
        $arr = array();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $output['status'] = "success";
            $output['data'] = $row;
        } else {
            $output['data'] = $arr;
        }
    }
    // get all users details
    elseif (isset($request['txtSearch'])) {
        $sql = "SELECT *, 
        SUBSTRING_INDEX(user.User_Name, ' ', 1) AS Firstname, 
        SUBSTRING_INDEX(user.User_Name, ' ', -1) AS Lastname 
        FROM user
        LEFT JOIN doctor ON doctor.Doctor_ID = user.User_ID
        LEFT JOIN employee ON employee.Emp_ID = user.User_ID";
        // $sql .= "SELECT * FROM patient";

        $txtSearch = $request['txtSearch'];

        if (strlen($txtSearch) > 0) {
            $sql .= " WHERE user.User_ID = '$txtSearch' 
            OR user.Username LIKE '%$txtSearch%' 
            OR doctor.Doctor_Name LIKE '%$txtSearch%' 
            OR employee.Emp_Name LIKE '%$txtSearch%'";
        }
        $result = $conn->query($sql);
        $arr = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                array_push($arr, $row);
            }
        }
        $output = $arr;
    }
} else if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    $content = file_get_contents('php://input');
    $request = json_decode($content, true);

    if (isset($request["updateid"])) {
        $Patient_ID = $request['updateid'];
        $Patient_Name = $request['myPatient_Name'];
        $Birthday = $request['Birthday'];
        $Address = $request['Address'];
        $Tel = $request['Tel'];
        $Allergic = $request['Allergic'];
        $sql = "UPDATE patient SET Patient_Name = '$Patient_Name', Birthday = '$Birthday', Address = '$Address', Tel = '$Tel', Allergic = '$Allergic' WHERE Patient_ID = '$Patient_ID'";
        $result = $conn->query($sql);
        if ($result === TRUE) {
            $output['status'] = "success";
            $output['message'] = "บันทึกข้อมูลสำเร็จ";
        } else {
            $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $conn->error;
        }
    }
} else {
    $output['message'] = "REQUEST_METHOD ผิดพลาด";
}
echo json_encode($output);
$conn->close();
?>