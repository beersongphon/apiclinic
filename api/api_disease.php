<?php
// header('Access-Control-Allow-Origin: *');
// header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
// header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With or other");
// header('Access-Control-Allow-Credentials: true');
// header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

//Please create users database inside phpmysql admin and create userdetails tabel and create id, email and username fields
include("./db_connect.php");

$output = array(
    "status" => "error"
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = file_get_contents('php://input');
    $request = json_decode($content, true);
    //Add user
    if (isset($request['myDiseaseName'])) {
        $Disease_ID = $request['myDiseaseID'];
        $Disease_Name = $request['myDiseaseName'];
        $Disease_Price = $request['myDiseasePrice'];
        $Disease_Detail = $request['myDiseaseDetail'];

        $sql = "SELECT * FROM disease WHERE Disease_Name = '$Disease_Name'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $output['message'] = "ไม่สามารถเพิ่มข้อมูลได้ เนื่องจากมีชื่อโรคนี้แล้ว";
        } else {
            $sql = "INSERT INTO disease (Disease_ID, Disease_Name, Disease_Price, Disease_Detail) VALUES ('$Disease_ID', '$Disease_Name', '$Disease_Price', '$Disease_Detail')";
            $result = $conn->query($sql);
            if ($result) {
                $output['status'] = "success";
                $output['message'] = "บันทึกข้อมูลสำเร็จ";
            } else {
                $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: '$conn->error'";
            }
        }
    }
    //Delete user
    elseif (isset($request['deleteid'])) {
        $Disease_ID = $request['deleteid'];

        $sql = "DELETE FROM disease WHERE Disease_ID = '$Disease_ID'";
        $result = $conn->query($sql);
        if ($result) {
            $output['status'] = "success";
            $output['message'] = "บันทึกข้อมูลสำเร็จ";
        } else {
            $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_error($conn);
        }
        
    }
    //Get single user details
    elseif (isset($request['diseaseid'])) {
        $sql = "SELECT * FROM disease";

        $Disease_ID = $request['diseaseid'];

        if (strlen($User_ID) > 0) {
            $sql .= " WHERE Disease_ID = '$Disease_ID'";
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
        $sql = "SELECT * FROM disease";
        // $sql .= "SELECT * FROM patient";

        $txtSearch = $request['txtSearch'];

        if (strlen($txtSearch) > 0) {
            $sql .= " WHERE Disease_ID = '$txtSearch' 
            OR Disease_Name LIKE '%$txtSearch%'";
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
        $Disease_ID = $request['updateid'];
        $Disease_Name = $request['myDiseaseName'];
        $Disease_Price = $request['myDiseasePrice'];
        $Disease_Detail = $request['myDiseaseDetail'];

        $sql = "UPDATE disease SET Disease_Name = '$Disease_Name', Disease_Detail = '$Disease_Detail', Disease_Price = '$Disease_Price' WHERE Disease_ID = '$Disease_ID'";
        $result = $conn->query($sql);
        if ($result === TRUE) {
            $output['status'] = "success";
            $output['message'] = "บันทึกข้อมูลสำเร็จ";
        } else {
            $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $conn->error;
        }
    }
} else if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    // $content = file_get_contents('php://input');
    // $request = json_decode($content, true);
    
    //Delete user
    if (isset($_GET['deleteid'])) {
        $Disease_ID = $_GET['deleteid'];

        $sql = "DELETE FROM disease WHERE Disease_ID = '$Disease_ID'";
        $result = $conn->query($sql);
        if ($result) {
            $output['status'] = "success";
            $output['message'] = "บันทึกข้อมูลสำเร็จ";
        } else {
            $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_error($conn);
        }
    }
} else {
    $output['message'] = "REQUEST_METHOD ผิดพลาด";
}
echo json_encode($output);
$conn->close();
?>
