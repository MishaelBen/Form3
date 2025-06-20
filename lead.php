<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Database config
$host = "127.0.0.1";     // or "localhost"
$port = 3307;            // your custom MySQL port
$username = "root";
$password = "";
$database = "leads";     // change if different

// 2. Connect to MySQL on custom port
$conn = new mysqli($host, $username, $password, $database, $port);

// 3. Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 4. Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and get inputs
    $name     = $_POST['name'] ?? '';
    $email    = $_POST['email'] ?? '';
    $phone    = $_POST['phone'] ?? '';
    $location = $_POST['location'] ?? '';
    $status   = $_POST['status'] ?? '';
    $source   = $_POST['source'] ?? '';

    // Optional: Basic validation
    if (!$name || !$email || !$phone || !$location || !$status || !$source) {
        echo "<script>alert('All fields are required.'); window.history.back();</script>";
        exit();
    }

    // Insert query
    $stmt = $conn->prepare("INSERT INTO client_leads (name, email, phone, location, status, source) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $phone, $location, $status, $source);

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
