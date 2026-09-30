<?php
session_start();
require("../includes/database_connect.php");

header('Content-Type: application/json');

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    echo json_encode(array("success" => false, "message" => "Please fill in all fields!"));
    exit;
}

// Prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo json_encode(array("success" => false, "message" => "Something went wrong!"));
    exit;
}

if (mysqli_num_rows($result) === 0) {
    echo json_encode(array("success" => false, "message" => "Invalid Email or password!"));
    exit;
}

$row = mysqli_fetch_assoc($result);

// Check password: first try modern password_verify, then fallback to legacy sha1
$password_matched = false;
if (password_verify($password, $row['password'])) {
    $password_matched = true;
} elseif (sha1($password) === $row['password'] || $password === $row['password']) {
    $password_matched = true;
    // Upgrade legacy password hash automatically to bcrypt!
    $new_hash = password_hash($password, PASSWORD_DEFAULT);
    $up_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $up_stmt->bind_param("si", $new_hash, $row['id']);
    $up_stmt->execute();
    $up_stmt->close();
}

if (!$password_matched) {
    echo json_encode(array("success" => false, "message" => "Invalid Email or password!"));
    exit;
}

$_SESSION['user_id'] = (int)$row['id'];
$_SESSION['full_name'] = $row['full_name'];
$_SESSION['email'] = $row['email'];

echo json_encode(array("success" => true, "message" => "Login successful!"));
$stmt->close();
mysqli_close($conn);
