<?php
session_start();

require "../includes/database_connect.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(array("success" => false, "is_logged_in" => false));
    return;
}

$user_id = (int)$_SESSION['user_id'];
$property_id = isset($_GET["property_id"]) ? (int)$_GET["property_id"] : 0;

if ($property_id <= 0) {
    echo json_encode(array("success" => false, "message" => "Invalid property ID"));
    return;
}

// Check if already interested — prepared statement
$stmt1 = $conn->prepare("SELECT * FROM interested_users_properties WHERE user_id = ? AND property_id = ?");
$stmt1->bind_param("ii", $user_id, $property_id);
$stmt1->execute();
$result_1 = $stmt1->get_result();

if (!$result_1) {
    echo json_encode(array("success" => false, "message" => "Something went wrong"));
    return;
}

if (mysqli_num_rows($result_1) > 0) {
    $stmt1->close();
    // Remove interest
    $stmt2 = $conn->prepare("DELETE FROM interested_users_properties WHERE user_id = ? AND property_id = ?");
    $stmt2->bind_param("ii", $user_id, $property_id);
    $result_2 = $stmt2->execute();
    $stmt2->close();
    if (!$result_2) {
        echo json_encode(array("success" => false, "message" => "Something went wrong"));
        return;
    } else {
        echo json_encode(array("success" => true, "is_interested" => false, "property_id" => $property_id));
        return;
    }
} else {
    $stmt1->close();
    // Add interest
    $stmt3 = $conn->prepare("INSERT INTO interested_users_properties (user_id, property_id) VALUES (?, ?)");
    $stmt3->bind_param("ii", $user_id, $property_id);
    $result_3 = $stmt3->execute();
    $stmt3->close();
    if (!$result_3) {
        echo json_encode(array("success" => false, "message" => "Something went wrong"));
        return;
    } else {
        echo json_encode(array("success" => true, "is_interested" => true, "property_id" => $property_id));
        return;
    }
}
