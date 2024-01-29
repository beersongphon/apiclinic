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
    //Add user{
    if (isset($request['myPatientID'])) {
        $Remedy_ID = $request['myRemedyID'];
        $Preliminary_ID = $request['myPreliminaryID'];
        $Patient_ID = $request['myPatientID'];
        $Doctor_ID = $request['myDoctorID'];
        $Disease_ID = $request['myDiseaseID'];
        $Remedy_Because = $request['myRemedyBecause'];

        $sql = "INSERT INTO remedy_detail (Remedy_ID, Disease_ID, Remedy_Because) VALUES ('$Remedy_ID', '$Disease_ID', '$Remedy_Because')";
        $result = $conn->query($sql);
        if ($result) {
            $sql = "INSERT INTO remedy (Remedy_ID, Preliminary_ID, Patient_ID, Doctor_ID) VALUES ('$Remedy_ID', '$Preliminary_ID', '$Patient_ID', '$Doctor_ID')";
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
            $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: '$conn->error'";
        }
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
    elseif (isset($request['remedyid'])) {
        $sql = "SELECT *, 
        SUBSTRING_INDEX( Patient_Name, ' ', 1 ) AS Firstname, 
        SUBSTRING_INDEX( Patient_Name, ' ', -1 ) AS Lastname 
        FROM remedy
        LEFT JOIN remedy_detail ON remedy_detail.Remedy_ID = remedy.Remedy_ID
        LEFT JOIN disease ON disease.Disease_ID = remedy_detail.Disease_ID
        LEFT JOIN preliminary ON preliminary.Preliminary_ID = remedy.Preliminary_ID
        LEFT JOIN patient ON patient.Patient_ID = remedy.Patient_ID
        LEFT JOIN card_patient ON card_patient.Patient_ID = patient.Patient_ID
        LEFT JOIN doctor ON doctor.Doctor_ID = remedy.Doctor_ID";

        $remedyid = $request['remedyid'];

        if (strlen($remedyid) > 0) {
            $sql .= " WHERE remedy.Remedy_ID = '$remedyid'";
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
        $sql = "SELECT * FROM preliminary
        LEFT JOIN preliminary_detail ON preliminary_detail.Preliminary_ID = preliminary.Preliminary_ID
        LEFT JOIN employee ON employee.Emp_ID = preliminary.Emp_ID
        LEFT JOIN patient ON patient.Patient_ID = preliminary.Patient_ID";
        // $sql .= "SELECT * FROM patient";

        $txtSearch = $request['txtSearch'];

        if (strlen($txtSearch) > 0) {
            $sql .= " WHERE patient.Patient_ID = '$txtSearch'";
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
        $Remedy_ID = $request['updateid'];
        $Preliminary_ID = $request['myPreliminaryID'];
        $Patient_ID = $request['myPatientID'];
        $Doctor_ID = $request['myDoctorID'];
        $Disease_ID = $request['myDiseaseID'];
        $Remedy_Because = $request['myRemedyBecause'];

        $sql = "UPDATE remedy_detail SET Disease_ID = '$Disease_ID', Remedy_Because = '$Remedy_Because' WHERE Remedy_ID = '$Remedy_ID'";
        $result = $conn->query($sql);
        if ($result) {
            $sql = "UPDATE remedy SET Preliminary_ID = '$Preliminary_ID', Patient_ID = '$Patient_ID', Doctor_ID = '$Doctor_ID' WHERE Remedy_ID = '$Remedy_ID'";
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
            $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: '$conn->error'";
        }
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
