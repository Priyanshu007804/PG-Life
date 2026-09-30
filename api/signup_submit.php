<?php
require("../includes/database_connect.php");

header('Content-Type: application/json');

$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$college_name = isset($_POST['college_name']) ? trim($_POST['college_name']) : '';
$gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';

if (empty($full_name) || empty($phone) || empty($email) || empty($password) || empty($gender)) {
    echo json_encode(array("success" => false, "message" => "Please fill all required fields!"));
    exit;
}

// Secure bcrypt password hashing
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if email already registered — prepared statement
$stmt1 = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt1->bind_param("s", $email);
$stmt1->execute();
$result = $stmt1->get_result();

if (!$result) {
    echo json_encode(array("success" => false, "message" => "Something went wrong!"));
    exit;
}

if (mysqli_num_rows($result) > 0) {
    echo json_encode(array("success" => false, "message" => "This email is already registered with us!"));
    $stmt1->close();
    exit;
}
$stmt1->close();

// Insert new user — prepared statement
$stmt2 = $conn->prepare("INSERT INTO users (email, password, full_name, phone, gender, college_name) VALUES (?, ?, ?, ?, ?, ?)");
$stmt2->bind_param("ssssss", $email, $hashed_password, $full_name, $phone, $gender, $college_name);
$result2 = $stmt2->execute();

if (!$result2) {
    echo json_encode(array("success" => false, "message" => "Failed to create account!"));
    exit;
}

echo json_encode(array("success" => true, "message" => "Your account has been created successfully!"));
$stmt2->close();
mysqli_close($conn);
