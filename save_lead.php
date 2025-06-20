<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli("localhost", "root", "", "leads");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get POST JSON data
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
  echo "❌ No data received.";
  exit;
}

// Prepare and escape data
$id = isset($data['id']) ? (int)$data['id'] : 0; // Get ID if exists
$name = $conn->real_escape_string($data['name'] ?? '');
$email = $conn->real_escape_string($data['email'] ?? '');
$phone = $conn->real_escape_string($data['phone'] ?? '');
$alt_phone = $conn->real_escape_string($data['alt_phone'] ?? '');
$business_details = $conn->real_escape_string($data['business_details'] ?? '');
$stage = $conn->real_escape_string($data['stage'] ?? '');
$phone_status = $conn->real_escape_string($data['phone_status'] ?? '');
$location = $conn->real_escape_string($data['location'] ?? '');
$loan_required = $conn->real_escape_string($data['loan_required'] ?? '');
$loan_amount = $conn->real_escape_string($data['loan_amount'] ?? '');
$links = $conn->real_escape_string($data['links'] ?? '');
$note = $conn->real_escape_string($data['note'] ?? '');
$status = $conn->real_escape_string($data['status'] ?? 'New');

if ($id > 0) {
  // Update existing record
  $sql = "UPDATE client_leads SET 
    name='$name', email='$email', phone='$phone', alt_phone='$alt_phone',
    business_details='$business_details', stage='$stage', phone_status='$phone_status',
    location='$location', loan_required='$loan_required', loan_amount='$loan_amount',
    links='$links', note='$note', status='$status'
    WHERE id=$id";
} else {
  // Insert new record
  $sql = "INSERT INTO client_leads 
    (name, email, phone, alt_phone, business_details, stage, phone_status,
     location, loan_required, loan_amount, links, note, status)
    VALUES (
      '$name', '$email', '$phone', '$alt_phone', '$business_details', '$stage',
      '$phone_status', '$location', '$loan_required', '$loan_amount', '$links', '$note', '$status'
    )";
}

if ($conn->query($sql) === TRUE) {
  echo $id > 0 ? "✅ Lead updated successfully!" : "✅ Lead saved successfully!";
} else {
  echo "❌ Error: " . $conn->error;
}


$conn->close();
?>
 