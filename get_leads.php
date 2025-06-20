<?php
header('Content-Type: application/json');
$host = "127.0.0.1";
$port = 3307;
$username = "root";
$password = "";
$database = "leads";

$conn = new mysqli($host, $username, $password, $database, $port);
if ($conn->connect_error) {
    die(json_encode(['error' => $conn->connect_error]));
}

// Get all leads
$sql = "SELECT * FROM client_leads ORDER BY created_at DESC";
$result = $conn->query($sql);

$leads = [];
while ($row = $result->fetch_assoc()) {
    // Decode timeline notes if stored as JSON
    $row['note'] = $row['note'] ? json_decode($row['note'], true) : [];
    $leads[] = $row;
}

echo json_encode($leads);
$conn->close();
