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
    if (isset($request['txtSearch']) && $request['myActionType'] == "A") {
        $sql = "SELECT *, 
        patient.Address AS Patient_Address, 
        (SELECT disease.Disease_Name FROM remedy_detail LEFT JOIN disease ON disease.Disease_ID = remedy_detail.Disease_ID WHERE remedy_detail.Remedy_ID = remedy.Remedy_ID LIMIT 1) AS Disease_Name,
        (SELECT prescription.Prescription_ID FROM prescription WHERE prescription.Remedy_ID = remedy.Remedy_ID LIMIT 1) AS Prescription_ID,
        (SELECT prescription.Prescription_Date FROM prescription WHERE prescription.Remedy_ID = remedy.Remedy_ID LIMIT 1) AS Prescription_Date,
        (SELECT payments_patient.Total_Price FROM payments_patient WHERE payments_patient.Remedy_ID = remedy.Remedy_ID LIMIT 1) AS Total_Price
        FROM remedy
        LEFT JOIN preliminary ON preliminary.Preliminary_ID = remedy.Preliminary_ID
        LEFT JOIN employee ON employee.Emp_ID = preliminary.Emp_ID
        LEFT JOIN patient ON patient.Patient_ID = remedy.Patient_ID
        LEFT JOIN card_patient ON card_patient.Patient_ID = patient.Patient_ID
        LEFT JOIN doctor ON doctor.Doctor_ID = remedy.Doctor_ID";
        // $sql .= "SELECT * FROM patient";

        $txtSearch = $request['txtSearch'];

        if (strlen($txtSearch) > 0) {
            $sql .= " WHERE remedy.Remedy_ID = '$txtSearch'
            OR doctor.Doctor_Name LIKE '%$txtSearch%'";
        }
        $result = $conn->query($sql);
        $arr = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $sqlprescription = "SELECT drug.Drug_Name, 
                prescription_detail.QTY_Drug,
                drug.Price,
                (prescription_detail.QTY_Drug * drug.Price) AS Total
                FROM prescription_detail
                LEFT JOIN prescription ON prescription.Prescription_ID = prescription_detail.Prescription_ID
                LEFT JOIN drug ON drug.Drug_ID = prescription_detail.Drug_ID              
                LEFT JOIN remedy ON remedy.Remedy_ID = prescription.Remedy_ID
                WHERE prescription.Remedy_ID = '$row[Remedy_ID]'";
                $resultprescription = $conn->query($sqlprescription);
                $arr_prescription = array();
                if ($resultprescription->num_rows > 0) {
                    while ($rowprescription = $resultprescription->fetch_assoc()) {
                        array_push($arr_prescription, $rowprescription);
                    }
                }
                array_push($arr,
                    array(
                        "Remedy_ID" => $row["Remedy_ID"],
                        "Remedy_Date" => $row["Remedy_Date"],
                        "Preliminary_ID" => $row["Preliminary_ID"],
                        "Patient_ID" => $row["Patient_ID"],
                        "Patient_Name" => $row["Patient_Name"],
                        "Patient_Address" => $row["Patient_Address"],
                        "Doctor_ID" => $row["Doctor_ID"],
                        "Doctor_Name" => $row["Doctor_Name"],
                        "Disease_Name" => $row["Disease_Name"],
                        "Emp_ID" => $row["Emp_ID"],
                        "Emp_Name" => $row["Emp_Name"],
                        "Remedy" => array(
                            "Remedy_ID" => $row["Remedy_ID"], 
                            "Disease_Name" => $row["Disease_Name"], 
                            "Total_Price" => $row["Total_Price"], 
                        ),
                        "Tel" => $row["Tel"],
                        "Prescription_ID" => $row["Prescription_ID"],
                        "Prescription_Date" => $row["Prescription_Date"],
                        "Prescription" => $arr_prescription,
                    )
                );
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
                $sqlimage = "SELECT *, order_detail.Amount AS Quantity FROM order_detail
                LEFT JOIN drug ON drug.Drug_ID = order_detail.Drug_ID
                WHERE Order_ID = '$row[Order_ID]'";
                $resultimage = $conn->query($sqlimage);
                $arr_image = array();
                if ($resultimage->num_rows > 0) {
                    while ($rowimage = $resultimage->fetch_assoc()) {
                        array_push($arr_image, $rowimage);
                    }
                }
                array_push(
                    $arr,
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
        $Order_ID = $request['updateid'];

        $sql = "UPDATE tb_order SET Order_Status = 'รับยาแล้ว' WHERE Order_ID = '$Order_ID'";
        $result = $conn->query($sql);
        if ($result) {
            $output['status'] = "success";
            $output['message'] = "บันทึกข้อมูลสำเร็จ";
        } else {
            $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: '$conn->error'";
        }
    }
} else {
    $output['message'] = "REQUEST_METHOD ผิดพลาด";
}
echo json_encode($output);
$conn->close();
?>