<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database config
$host = "127.0.0.1";
$port = 3306;
$username = "root";
$password = "";
$database = "leads";

// Connect
$conn = new mysqli($host, $username, $password, $database, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Only accept POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get all fields
    $id           = $_POST['id'] ?? null;
    $name         = $_POST['name'] ?? '';
    $email        = $_POST['email'] ?? '';
    $phone        = $_POST['phone'] ?? '';
    $altPhone     = $_POST['altPhone'] ?? '';
    $business     = $_POST['business'] ?? '';
    $status       = $_POST['status'] ?? '';
    $phone_status = $_POST['phone_status'] ?? '';
    $location     = $_POST['location'] ?? '';
    $loan_required = $_POST['loan_required'] ?? '';
    $loan_amount  = $_POST['loan_amount'] ?? '';
    $lead_source  = $_POST['lead_source'] ?? '';
    $stage        = $_POST['stage'] ?? '';
    $links        = isset($_POST['links']) ? json_encode($_POST['links']) : '[]';

    if (!$id) {
        echo "Error: Missing lead ID.";
        exit;
    }

    // Update existing lead by ID
    $sql = "UPDATE client_leads SET 
        name = ?, email = ?, phone = ?, alt_phone = ?, business = ?, 
        status = ?, phone_status = ?, location = ?, loan_required = ?, 
        loan_amount = ?, lead_source = ?, stage = ?, links = ? 
        WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssss", 
        $name, $email, $phone, $altPhone, $business, 
        $status, $phone_status, $location, $loan_required, 
        $loan_amount, $lead_source, $stage, $links, $id);

    if ($stmt->execute()) {
        echo "Lead updated successfully!";
    } else {
        echo "Update failed: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request method.";
}

$conn->close();
?>
