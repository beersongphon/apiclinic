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
include("./config/db_connect.php");

$output = array(
    "status" => "error"
);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = file_get_contents('php://input');
    $request = json_decode($content, true);
    //Add user
    if (isset($request['myDrugName'])) {
        $Drug_ID = $request['myDrugID'];
        $Drug_Name = $request['myDrugName'];
        $Mfg_Date = $request['myMfgDate'];
        $Exp_Date = $request['myExpDate'];
        $Relate = $request['myRelate'];
        $Notation = $request['myNotation'];

        $sql = "SELECT * FROM drug WHERE Drug_Name = '$Drug_Name'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $output['message'] = "ไม่สามารถเพิ่มข้อมูลได้ เนื่องจากมีชื่อยาหรืออุปกรณ์แล้ว";
        } else {
            $sql = "INSERT INTO drug (Drug_ID, Drug_Name, Mfg_Date, Exp_Date, Relate, Notation) VALUES ('$Drug_ID', '$Drug_Name', '$Mfg_Date', '$Exp_Date', '$Relate', '$Notation')";
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
        $Drug_ID = $request['deleteid'];

        $sql = "DELETE FROM drug WHERE Drug_ID = '$Drug_ID'";
        $result = $conn->query($sql);
        if ($result) {
            $output['status'] = "success";
            $output['message'] = "บันทึกข้อมูลสำเร็จ";
        } else {
            $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_error($conn);
        }
        
    }
    //Get single user details
    elseif (isset($request['drugid'])) {
        $sql = "SELECT * FROM drug";

        $Drug_ID = $request['drugid'];

        if (strlen($Drug_ID) > 0) {
            $sql .= " WHERE Drug_ID = '$Drug_ID'";
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
        $sql = "SELECT * FROM drug";
        // $sql .= "SELECT * FROM patient";

        $txtSearch = $request['txtSearch'];

        if (strlen($txtSearch) > 0) {
            $sql .= " WHERE Drug_ID = '$txtSearch' 
            OR Drug_Name LIKE '%$txtSearch%'";
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
        $Drug_ID = $request['updateid'];
        $Drug_Name = $request['myDrugName'];
        $Mfg_Date = $request['myMfgDate'];
        $Exp_Date = $request['myExpDate'];
        $Relate = $request['myRelate'];
        $Notation = $request['myNotation'];

        $sql = "UPDATE drug SET Drug_Name = '$Drug_Name', Mfg_Date = '$Mfg_Date', Exp_Date = '$Exp_Date', Relate = '$Relate', Notation = '$Notation' WHERE Drug_ID = '$Drug_ID'";
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
        $Drug_ID = $_GET['deleteid'];

        $sql = "DELETE FROM drug WHERE Drug_ID = '$Drug_ID'";
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
