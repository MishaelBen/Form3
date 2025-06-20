<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Database config
$host = "127.0.0.1";
$port = 3306;
$username = "root";
$password = "";
$database = "leads";

// 2. Connect to MySQL
$conn = new mysqli($host, $username, $password, $database, $port);

// 3. Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 4. Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and get inputs
    $name        = $_POST['name'] ?? '';
    $email       = $_POST['email'] ?? '';
    $phone       = $_POST['phone'] ?? '';
    $location    = $_POST['location'] ?? '';
    $stage       = $_POST['stage'] ?? '';
    $lead_source = $_POST['lead_source'] ?? '';

    // Basic validation
    if (!$name || !$email || !$phone || !$location || !$stage || !$lead_source) {
        echo "<script>alert('All fields are required.'); window.history.back();</script>";
        exit();
    }

    // Insert query
    $stmt = $conn->prepare("INSERT INTO client_leads (name, email, phone, location, stage, lead_source) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $phone, $location, $stage, $lead_source);

    if ($stmt->execute()) {
        echo "<script>alert('Lead added successfully!'); window.location.href='leads.html';</script>";
    } else {
        echo "Error inserting lead: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request method.";
}

$conn->close();
?>
