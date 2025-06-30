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
    $id            = $_POST['id'] ?? null;
    $name          = $_POST['name'] ?? '';
    $email         = $_POST['email'] ?? '';
    $phone         = $_POST['phone'] ?? '';
    $altPhone      = $_POST['altPhone'] ?? '';
    $status        = $_POST['status'] ?? '';
    $phone_status  = $_POST['phone_status'] ?? '';
    $location      = $_POST['location'] ?? '';
    $loan_required = $_POST['loan_required'] ?? '';
    $loan_amount   = $_POST['loan_amount'] ?? '';
    $lead_source   = $_POST['lead_source'] ?? '';
    $stage         = $_POST['stage'] ?? '';
    $links         = isset($_POST['links']) ? json_encode($_POST['links']) : '[]';

    if ($id) {
        // UPDATE existing lead
        $sql = "UPDATE client_leads SET 
            name = ?, email = ?, phone = ?, alt_phone = ?, 
            status = ?, phone_status = ?, location = ?, loan_required = ?, 
            loan_amount = ?, lead_source = ?, stage = ?, links = ? 
            WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssssi", 
            $name, $email, $phone, $altPhone, 
            $status, $phone_status, $location, $loan_required, 
            $loan_amount, $lead_source, $stage, $links, $id);

        $stmt->execute();
        $stmt->close();

        header("Location: leads.html");
        exit();
    } else {
        // INSERT new lead
        $sql = "INSERT INTO client_leads (
            name, email, phone, alt_phone, 
            status, phone_status, location, loan_required, 
            loan_amount, lead_source, stage, links
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssss", 
            $name, $email, $phone, $altPhone, 
            $status, $phone_status, $location, $loan_required, 
            $loan_amount, $lead_source, $stage, $links);

        $stmt->execute();
        $stmt->close();

        header("Location: leads.html");  // ✅ This refreshes the page
        exit();
    }
} else {
    echo "Invalid request method.";
}

$conn->close();
?>
