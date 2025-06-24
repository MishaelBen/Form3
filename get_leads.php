<?php
header('Content-Type: application/json');

// Database config
$host = "127.0.0.1";
$port = 3306;
$username = "root";
$password = "";
$database = "leads";

// Connect to MySQL
$conn = new mysqli($host, $username, $password, $database, $port);

// Handle connection error
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// Run query to fetch leads
$sql = "SELECT * FROM client_leads ORDER BY created_at DESC";
$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => "Query error: " . $conn->error]);
    exit;
}

$leads = [];
while ($row = $result->fetch_assoc()) {
    // Safely decode `note` JSON if present
    if (!empty($row['note'])) {
        $decodedNote = json_decode($row['note'], true);
        $row['note'] = is_array($decodedNote) ? $decodedNote : [];
    } else {
        $row['note'] = [];
    }

    $leads[] = $row;
}

echo json_encode($leads);
$conn->close();
?>
