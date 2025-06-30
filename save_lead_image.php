<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
$config = [
    'upload_dir' => __DIR__ . '/uploads/lead_images/',
    'max_file_size' => 5 * 1024 * 1024, // 5MB
    'allowed_types' => ['jpg', 'jpeg', 'png', 'gif'],
    'public_base_url' => '/uploads/lead_images/'
];

// Logging start
file_put_contents("upload_debug.log", "\n🟢 Script started at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

// DB connection
$conn = new mysqli("localhost", "root", "", "leads");
if ($conn->connect_error) {
    error_log("❌ DB connection failed: {$conn->connect_error}\n", 3, "upload_debug.log");
    http_response_code(500);
    exit("Database connection error");
}

// Validate POST and files
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['images'])) {
    error_log("❌ Invalid request or no files uploaded\n", 3, "upload_debug.log");
    http_response_code(400);
    exit("Invalid request");
}

// Validate lead ID
$leadId = filter_input(INPUT_POST, 'lead_id', FILTER_VALIDATE_INT);
if (!$leadId || $leadId <= 0) {
    error_log("❌ Invalid lead_id: " . ($_POST['lead_id'] ?? 'NULL') . "\n", 3, "upload_debug.log");
    exit("Invalid lead ID");
}

// Check if lead exists
$stmt = $conn->prepare("SELECT 1 FROM client_leads WHERE id = ?");
$stmt->bind_param("i", $leadId);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    error_log("❌ Lead ID $leadId not found in DB\n", 3, "upload_debug.log");
    exit("Lead not found");
}

// Ensure upload directory exists
if (!is_dir($config['upload_dir'])) {
    if (!mkdir($config['upload_dir'], 0755, true)) {
        error_log("❌ Failed to create upload dir\n", 3, "upload_debug.log");
        http_response_code(500);
        exit("Server error");
    }
}

// Process multiple files (up to 5)
$files = $_FILES['images'];
$totalFiles = count($files['name']);
$maxUploads = min($totalFiles, 5);

$successfulUploads = 0;

for ($i = 0; $i < $maxUploads; $i++) {
    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
        error_log("⚠️ Skipped file $i due to error code: " . $files['error'][$i] . "\n", 3, "upload_debug.log");
        continue;
    }

    $originalName = preg_replace('/[^a-zA-Z0-9\.\-_]/', '', $files['name'][$i]);
    $tmpPath = $files['tmp_name'][$i];
    $size = $files['size'][$i];
    $type = $files['type'][$i];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    // Validations
    if (!in_array($ext, $config['allowed_types'])) {
        error_log("⚠️ Skipped file $originalName due to invalid type\n", 3, "upload_debug.log");
        continue;
    }

    if (!getimagesize($tmpPath)) {
        error_log("⚠️ Skipped file $originalName — not a valid image\n", 3, "upload_debug.log");
        continue;
    }

    if ($size > $config['max_file_size']) {
        error_log("⚠️ Skipped file $originalName — too large\n", 3, "upload_debug.log");
        continue;
    }

    $uniqueName = "lead_{$leadId}_" . bin2hex(random_bytes(6)) . '.' . $ext;
    $targetPath = $config['upload_dir'] . $uniqueName;
    $publicPath = $config['public_base_url'] . $uniqueName;

    // Save file and insert DB record
    if (move_uploaded_file($tmpPath, $targetPath)) {
        chmod($targetPath, 0644);

        $stmt = $conn->prepare("INSERT INTO lead_images 
            (lead_id, file_name, file_path, file_size, mime_type, upload_time) 
            VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("issis", $leadId, $originalName, $publicPath, $size, $type);
        $stmt->execute();
        $stmt->close();

        $successfulUploads++;
        error_log("✅ Uploaded: $originalName\n", 3, "upload_debug.log");
    } else {
        error_log("❌ Failed to save file: $originalName\n", 3, "upload_debug.log");
    }
}

$conn->close();

if ($successfulUploads > 0) {
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit();
} else {
    error_log("❌ No files were successfully uploaded\n", 3, "upload_debug.log");
    http_response_code(400);
    exit("No valid images uploaded");
}
?>
