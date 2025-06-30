<?php
header('Content-Type: application/json');

require_once 'db_config.php';

if (!isset($_GET['lead_id'])) {
    echo json_encode([]);
    exit;
}

$leadId = $_GET['lead_id'];

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare(
        "SELECT id, file_name as name, file_path as url, file_size as size, 
        mime_type as type, uploaded_at 
        FROM lead_images 
        WHERE lead_id = :lead_id 
        ORDER BY uploaded_at DESC"
    );
    
    $stmt->execute([':lead_id' => $leadId]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($images);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>