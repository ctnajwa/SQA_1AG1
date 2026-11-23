<?php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = [];

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($input['id']) && $input['id'] !== '' ? filter_var($input['id'], FILTER_VALIDATE_INT) : null;
    $name = trim((string)($input['name'] ?? ''));
    $category = (string)($input['category'] ?? '');
    $quantity = filter_var($input['quantity'] ?? null, FILTER_VALIDATE_FLOAT);
    $unit = (string)($input['unit'] ?? '');
    $expiry = (string)($input['expiry'] ?? '');
    $categories = ['Baking', 'Dairy', 'Fruits', 'Sweeteners', 'Others'];
    $units = ['KG', 'G', 'PCS', 'L', 'ML'];

    if (($id === false) || $name === '' || strlen($name) > 100 || !in_array($category, $categories, true)
        || $quantity === false || $quantity < 0 || !in_array($unit, $units, true)
        || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiry)) {
        respond(422, ['success' => false, 'message' => 'Please enter valid ingredient information.']);
    }

    if ($id) {
        $stmt = $conn->prepare('UPDATE ingredients SET ingredient_name = ?, category = ?, quantity = ?, unit = ?, expiry_date = ? WHERE ingredient_id = ?');
        if (!$stmt) respond(500, ['success' => false, 'message' => 'Could not prepare ingredient update.']);
        $stmt->bind_param('ssdssi', $name, $category, $quantity, $unit, $expiry, $id);
    } else {
        $stmt = $conn->prepare('INSERT INTO ingredients (ingredient_name, category, quantity, unit, expiry_date) VALUES (?, ?, ?, ?, ?)');
        if (!$stmt) respond(500, ['success' => false, 'message' => 'Could not prepare ingredient insert.']);
        $stmt->bind_param('ssdss', $name, $category, $quantity, $unit, $expiry);
    }

    if (!$stmt->execute()) {
        $duplicate = $stmt->errno === 1062;
        $stmt->close();
        respond($duplicate ? 409 : 500, ['success' => false, 'message' => $duplicate ? 'This ingredient already exists.' : 'Unable to save ingredient.']);
    }
    $savedId = $id ?: $conn->insert_id;
    $stmt->close();

    $stmt = $conn->prepare('SELECT ingredient_id AS id, ingredient_name AS name, category, quantity, unit, expiry_date AS expiry FROM ingredients WHERE ingredient_id = ?');
    $stmt->bind_param('i', $savedId);
    $stmt->execute();
    $stmt->bind_result($savedId, $savedName, $savedCategory, $savedQuantity, $savedUnit, $savedExpiry);
    $stmt->fetch();
    $stmt->close();
    respond(200, ['success' => true, 'ingredient' => [
        'id' => (int)$savedId, 'name' => $savedName, 'category' => $savedCategory,
        'quantity' => (float)$savedQuantity, 'unit' => $savedUnit, 'expiry' => $savedExpiry
    ]]);
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
    if (!$id || $id < 1) respond(422, ['success' => false, 'message' => 'Invalid ingredient ID.']);
    $stmt = $conn->prepare('DELETE FROM ingredients WHERE ingredient_id = ?');
    if (!$stmt) respond(500, ['success' => false, 'message' => 'Could not prepare ingredient deletion.']);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $deleted = $stmt->affected_rows > 0;
    $stmt->close();
    if (!$deleted) respond(404, ['success' => false, 'message' => 'Ingredient not found.']);
    respond(200, ['success' => true]);
}

respond(405, ['success' => false, 'message' => 'Unsupported ingredient request.']);
