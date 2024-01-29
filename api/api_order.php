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
    $content = file_get_contents('php://input'); //Read the JSON file in PHP
    $request = json_decode($content, true); //Convert JSON String into PHP Array

    //Add user{
    if (isset($request['myOrderID'])) {
        $Order_ID  = $request['myOrderID'];
        $Dealer_ID = $request['myDealerID'];
        $Emp_ID = $request['myEmpID'];
        $Total_Price  = $request['myTotalPrice'];

        $sql = "INSERT INTO tb_order (Order_ID, Dealer_ID, Emp_ID, Total_Price) VALUES ('$Order_ID', '$Dealer_ID', '$Emp_ID', '$Total_Price')";
        $result = $conn->query($sql);
        if ($result) {
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
            $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_error($conn);
        }
    }
    //Get single user details
    elseif (isset($request['appid'])) {
        $sql = "SELECT *, 
        SUBSTRING_INDEX( Patient_Name, ' ', 1 ) AS Firstname, 
        SUBSTRING_INDEX( Patient_Name, ' ', -1 ) AS Lastname, 
        (SELECT DISTINCT prescription.Prescription_ID FROM prescription WHERE prescription.Remedy_ID = remedy.Remedy_ID LIMIT 1) AS Prescription_ID,
        (SELECT DISTINCT (disease.Disease_Price+dispense.Total_Amount) FROM dispense WHERE dispense.Prescription_ID = Prescription_ID LIMIT 1) AS Total_Price 
        FROM appoint 
        LEFT JOIN appoint_detail ON appoint_detail.App_ID = appoint.App_ID
        LEFT JOIN remedy ON remedy.Remedy_ID = appoint.Remedy_ID
        LEFT JOIN remedy_detail ON remedy_detail.Remedy_ID = remedy.Remedy_ID
        LEFT JOIN disease ON disease.Disease_ID = remedy_detail.Disease_ID
        LEFT JOIN patient ON patient.Patient_ID = appoint.Patient_ID
        LEFT JOIN card_patient ON card_patient.Patient_ID = patient.Patient_ID
        LEFT JOIN doctor ON doctor.Doctor_ID = appoint.Doctor_ID";

        $App_ID = $request['appid'];

        if (strlen($App_ID) > 0) {
            $sql .= " WHERE appoint.App_ID = '$App_ID'";
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
    //Get single user details
    elseif (isset($request['prescriptionid']) && isset($request['txtSearch'])) {
        $sql = "SELECT *, (prescription_detail.QTY_Drug * drug.Price) AS Prices  FROM prescription_detail
        LEFT JOIN prescription ON prescription.Prescription_ID = prescription_detail.Prescription_ID
        LEFT JOIN drug ON drug.Drug_ID = prescription_detail.Drug_ID";

        $txtSearch = $request['txtSearch'];

        if (strlen($txtSearch) > 0) {
            $sql .= " WHERE prescription_detail.Prescription_ID = '$txtSearch'";
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
    // get all users details
    elseif (isset($request['txtSearch'])) {
        $sql = "SELECT * 
        FROM tb_order
        LEFT JOIN dealer ON dealer.Dealer_ID = tb_order.Dealer_ID
        LEFT JOIN employee ON employee.Emp_ID = tb_order.Emp_ID";
        // $sql .= "SELECT * FROM patient";

        $txtSearch = $request['txtSearch'];

        if (strlen($txtSearch) > 0) {
            $sql .= " WHERE Order_ID = '$txtSearch'
            OR dealer.Dealer_Name LIKE '%$txtSearch%'
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
    // get all users details
    elseif (isset($request['txtSearchs'])) {
        $sql = "SELECT *,
        tb_order.Order_ID AS Order_Name
        FROM tb_order
        LEFT JOIN dealer ON dealer.Dealer_ID = tb_order.Dealer_ID
        LEFT JOIN employee ON employee.Emp_ID = tb_order.Emp_ID
        WHERE tb_order.Order_Status = 'รอรับยา'";
        // $sql .= "SELECT * FROM patient";

        $txtSearch = $request['txtSearchs'];

        if (strlen($txtSearch) > 0) {
            $sql .= " AND tb_order.Order_ID = '$txtSearch'
            OR dealer.Dealer_Name LIKE '%$txtSearch%'
            OR employee.Emp_Name LIKE '%$txtSearch%'";
        }
        $result = $conn->query($sql);
        $arr = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $sqlimage = "SELECT *, order_detail.Amount FROM order_detail
                LEFT JOIN drug ON drug.Drug_ID = order_detail.Drug_ID
                WHERE Order_ID = '$row[Order_ID]'";
                $resultimage = $conn->query($sqlimage);
                $arr_image = array();
                if ($resultimage->num_rows > 0) {
                    while ($rowimage = $resultimage->fetch_assoc()) {
                        array_push($arr_image, $rowimage);
                    }
                }
                array_push($arr,
                    array(
                        "Order_ID" => $row["Order_ID"],
                        "Order_Date" => $row["Order_Date"],
                        "Dealer_ID" => $row["Dealer_ID"],
                        "Emp_ID" => $row["Emp_ID"],
                        "Total_Price" => $row["Total_Price"],
                        "Order_Status" => $row["Order_Status"],
                        "Dealer_Name" => $row["Dealer_Name"],
                        "Address" => $row["Address"],
                        "Email" => $row["Email"],
                        "Tel" => $row["Tel"],
                        "Emp_Name" => $row["Emp_Name"],
                        "Salary" => $row["Salary"],
                        "Department" => $row["Department"],
                        "Order_Name" => $row["Order_ID"],
                        "result" => $arr_image,
                    )
                );
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