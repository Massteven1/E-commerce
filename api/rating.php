<?php
require_once '../config/config.php';
require_once '../models/Rating.php';

header('Content-Type: application/json');

// Verificar autenticación
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['course_id']) || !isset($input['rating'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Course ID and rating are required']);
    exit;
}

$ratingModel = new Rating();
$ratingModel->course_id = $input['course_id'];
$ratingModel->user_id = $_SESSION['user_id'];
$ratingModel->rating = $input['rating'];
$ratingModel->review = $input['review'] ?? '';

// Validar rating
if ($ratingModel->rating < 1 || $ratingModel->rating > 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Rating must be between 1 and 5']);
    exit;
}

$success = $ratingModel->rateCourse();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Rating saved successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save rating']);
}
?>
