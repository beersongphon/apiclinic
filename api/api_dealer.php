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
    if (isset($request['myDealerID']) && isset($request['myDealername'])) {
        $Dealer_ID = $request['myDealerID'];
        $Dealer_Name = $request['myDealername'];
        $Address = $request['myAddress'];
        $Email = $request['myEmail'];
        $Tel = $request['myTel'];

        $sql = "SELECT * FROM dealer WHERE Dealer_Name = '$Dealer_Name'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $output['message'] = "ไม่สามารถเพิ่มข้อมูลได้ เนื่องจากผู้ตัวแทนจำหน่ายนี้มีชื่อแล้ว";
        } else {
            $sql = "INSERT INTO dealer (Dealer_ID, Dealer_Name, Address, Email, Tel) VALUES ('$Dealer_ID', '$Dealer_Name', '$Address', '$Email', '$Tel')";
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
        $Dealer_ID = $request['deleteid'];

        $sql = "DELETE FROM dealer WHERE Dealer_ID = '$Dealer_ID'";
        $result = $conn->query($sql);
        if ($result) {
            $output['status'] = "success";
            $output['message'] = "บันทึกข้อมูลสำเร็จ";
        } else {
            $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_error($conn);
        }
        
    }
    //Get single user details
    elseif (isset($request['dealerid'])) {
        $sql = "SELECT *,
        SUBSTRING_INDEX( Patient_Name, ' ', 1 ) AS Firstname, 
        SUBSTRING_INDEX( Patient_Name, ' ', -1 ) AS Lastname 
        FROM dealer";

        $Dealer_ID = $request['dealerid'];

        if (strlen($User_ID) > 0) {
            $sql .= " WHERE Dealer_ID = '$Dealer_ID'";
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
        SUBSTRING_INDEX(Dealer_Name, ' ', 1) AS Firstname, 
        SUBSTRING_INDEX(Dealer_Name, ' ', -1) AS Lastname
        FROM dealer";
        // $sql .= "SELECT * FROM patient";

        $txtSearch = $request['txtSearch'];

        if (strlen($txtSearch) > 0) {
            $sql .= " WHERE Dealer_ID = '$txtSearch' 
            OR Dealer_Name LIKE '%$txtSearch%' 
            OR Email LIKE '%$txtSearch%' 
            OR Tel LIKE '%$txtSearch%'";
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
        $Dealer_ID = $request['updateid'];
        $Dealer_Name = $request['myDealername'];
        $Address = $request['myAddress'];
        $Email = $request['myEmail'];
        $Tel = $request['myTel'];
        $sql = "UPDATE dealer SET Dealer_Name = '$Dealer_Name', Address = '$Address', Email = '$Email', Tel = '$Tel' WHERE Dealer_ID = '$Dealer_ID'";
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
        $Dealer_ID = $_GET['deleteid'];

        $sql = "DELETE FROM dealer WHERE Dealer_ID = '$Dealer_ID'";
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
