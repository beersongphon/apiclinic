<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
date_default_timezone_set("Asia/Bangkok");

include("./db_connect.php");

$output = array(
    "status" => "error"
);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sql = '';
    $table_data = '';

    $content = file_get_contents('php://input'); //Read the JSON file in PHP
    $array = json_decode($content, true); //Convert JSON String into PHP Array
    foreach ($array as $row) {
        $Order_ID = $row['myOrderID'];
        $Drug_ID = $row['myDrugID'];
        $Amount = $row['myAmount'];
        $Net_Price = $row['myNetPrice'];

        $sql .= "INSERT INTO order_detail (Order_ID, Drug_ID, Amount, Net_Price) 
        VALUES ('$Order_ID', '$Drug_ID', '$Amount', '$Net_Price');"; // Make Multiple Insert Query 
        $arr_data = array();
        $jsonRow = array(
            "Order_ID"=> $Order_ID,
            "Drug_ID"=> $Drug_ID,
            "Amount"=> $Amount,
            "Net_Price"=> $Net_Price
        );
        // $table_data .= array_push($arr_data,$sql); //Data for display on Web page
        $table_data .= array_push($arr_data, $jsonRow);
        // $table_data .= '
        //     <tr>
        //         <td>' . $row["Prescription_ID"] . '</td>
        //         <td>' . $row["Drug_ID"] . '</td>
        //         <td>' . $row["QTY_Drug"] . '</td>
        //     </tr>
        //    '; //Data for display on Web page
    }
    $result = $conn->multi_query($sql);
    if ($result) {
        // echo $table_data;
        // echo json_encode([$jsonRow]);
        $output['status'] = "success";
        $output['message'] = "บันทึกข้อมูลสำเร็จ";
    } else {
        $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: '$conn->error'";
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "PUT") {
    $sql = '';
    $table_data = '';

    $content = file_get_contents('php://input'); //Read the JSON file in PHP
    $array = json_decode($content, true); //Convert JSON String into PHP Array
    foreach ($array as $row) {
        $Order_ID = $row['myOrderID'];
        $Amount = $row['myAmount'];

        $sql .= "UPDATE order_detail SET Amount = '$Amount' WHERE (Order_ID = '$Order_ID');"; // Make Multiple Insert Query 
        $arr_data = array();
        $jsonRow = array(
            "Order_ID"=> $Order_ID,
            "Amount"=> $Amount
        );
        // $table_data .= array_push($arr_data,$sql); //Data for display on Web page
        $table_data .= array_push($arr_data, $jsonRow);
        // $table_data .= '
        //     <tr>
        //         <td>' . $row["Prescription_ID"] . '</td>
        //         <td>' . $row["QTY_Drug"] . '</td>
        //     </tr>
        //    '; //Data for display on Web page
    }
    $result = $conn->multi_query($sql);
    if ($result) {
        // echo $table_data;
        // echo json_encode([$jsonRow]);
        $output['status'] = "success";
        $output['message'] = "บันทึกข้อมูลสำเร็จ";
    } else {
        $output['message'] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: '$conn->error'";
    }
} else {
    $output['message'] = "REQUEST_METHOD ผิดพลาด";
}
echo json_encode($output);
$conn->close();

?>

 