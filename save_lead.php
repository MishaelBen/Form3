<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Database connection
$conn = new mysqli("localhost", "root", "", "leads");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// ✅ Get POST data from form
$name             = $_POST['name'] ?? '';
$email            = $_POST['email'] ?? '';
$phone            = $_POST['phone'] ?? '';
$alt_phone        = $_POST['alt_phone'] ?? '';
$business_details = $_POST['business_details'] ?? '';
$stage            = $_POST['stage'] ?? '';
$phone_status     = $_POST['phone_status'] ?? '';
$location         = $_POST['location'] ?? '';
$loan_required    = $_POST['loan_required'] ?? '';
$loan_amount      = $_POST['loan_amount'] ?? '';
$links            = $_POST['links'] ?? '';
$note             = $_POST['note'] ?? '';
$status           = $_POST['status'] ?? 'New';

// ✅ Check if lead exists
$check = $conn->prepare("SELECT id FROM client_leads WHERE email = ? LIMIT 1");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result && $result->num_rows > 0) {
    // update lead
    $row = $result->fetch_assoc();
    $lead_id = $row['id'];

    $update = $conn->prepare("UPDATE client_leads SET 
        name=?, phone=?, alt_phone=?, business_details=?, stage=?, phone_status=?,
        location=?, loan_required=?, loan_amount=?, links=?, note=?, status=?
        WHERE id=?");
    $update->bind_param("ssssssssssssi", $name, $phone, $alt_phone, $business_details, $stage,
        $phone_status, $location, $loan_required, $loan_amount,
        $links, $note, $status, $lead_id);
    $update->execute();

    $action = "updated";
} else {
    // insert new lead
    $insert = $conn->prepare("INSERT INTO client_leads 
        (name, email, phone, alt_phone, business_details, stage, phone_status,
         location, loan_required, loan_amount, links, note, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->bind_param("sssssssssssss", $name, $email, $phone, $alt_phone, $business_details,
        $stage, $phone_status, $location, $loan_required, $loan_amount,
        $links, $note, $status);
    $insert->execute();
    $lead_id = $insert->insert_id;
    $lead_id = $conn->insert_id;


    $action = "saved";
}

// ✅ Handle image if uploaded
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $image_name = time() . "_" . basename($_FILES["image"]["name"]);
    $image_path = $upload_dir . $image_name;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
        $stmt = $conn->prepare("INSERT INTO lead_images (lead_id, image_path) VALUES (?, ?)");
        $stmt->bind_param("is", $lead_id, $image_path);
        $stmt->execute();
    } else {
        echo json_encode(["success" => false, "message" => "❌ Image upload failed."]);
        exit;
    }
}

echo json_encode(["success" => true, "message" => "✅ Lead $action successfully", "lead_id" => $lead_id]);
$conn->close();
?>
