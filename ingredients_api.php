<?php
declare(strict_types=1);

/**
 * Ingredient module API. POST JSON:
 *   ?action=save    {id: null | positive integer, name, category, quantity, unit, expiry}
 *   ?action=delete  {id: positive integer}
 *
 * Returns: {success: true, ingredient: {...}} for save;
 *          {success: true} for delete.
 *
 * Keep this file in the same folder as admin_ingredient.php and db.php.
 */
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');  // Avoid leaking SQL errors into JSON.

require_once __DIR__ . '/ingredient_validation.php';
require_once __DIR__ . '/ingredient_repository.php';

function ingredient_reply(int $status, array $body): void
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function ingredient_positive_id($raw): int
{
    $id = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        throw new IngredientValidationError('id', 'A valid ingredient ID is required.');
    }
    return (int)$id;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    ingredient_reply(405, ['success' => false, 'message' => 'Only POST requests are allowed.']);
}

$action = $_GET['action'] ?? '';
if (!in_array($action, ['save', 'delete'], true)) {
    ingredient_reply(400, ['success' => false, 'message' => 'Unknown ingredient action.']);
}

try {
    $raw = file_get_contents('php://input');
    if ($raw === false || strlen($raw) > 65536) {
        ingredient_reply(413, ['success' => false, 'message' => 'Request body is too large.']);
    }
    $input = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
    if (!is_array($input)) {
        ingredient_reply(400, ['success' => false, 'message' => 'Expected a JSON object.']);
    }

    // Reject invalid requests BEFORE contacting MySQL.
    if ($action === 'save') {
        $value = validate_ingredient_payload($input);
        $rawId = $input['id'] ?? null;
        $id = ($rawId === null || $rawId === '') ? null : ingredient_positive_id($rawId);
    } else {
        $id = ingredient_positive_id($input['id'] ?? null);
    }

    // db.php is the existing shared connection file; it is NOT modified in this package.
    require __DIR__ . '/db.php';

    if (!isset($conn) || !($conn instanceof mysqli)) {
        throw new RuntimeException('MySQL connection is unavailable.');
    }
    $conn->set_charset('utf8mb4');
    // Make database errors catchable even if db.php disables MySQLi reports.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    if ($action === 'save') {
        $saved = ingredient_save($conn, $id, $value);
        if ($saved === null) {
            ingredient_reply(404, ['success' => false, 'message' => 'Ingredient not found.']);
        }
        ingredient_reply(200, ['success' => true, 'ingredient' => $saved]);
    }

    if (!ingredient_delete($conn, $id)) {
        ingredient_reply(404, ['success' => false, 'message' => 'Ingredient not found.']);
    }
    ingredient_reply(200, ['success' => true]);
} catch (IngredientValidationError $error) {
    ingredient_reply(422, [
        'success' => false,
        'field' => $error->field,
        'message' => $error->getMessage(),
    ]);
} catch (JsonException $error) {
    ingredient_reply(400, ['success' => false, 'message' => 'Malformed JSON request.']);
} catch (mysqli_sql_exception $error) {
    error_log('Ingredient DB error: ' . $error->getMessage());
    if ($error->getCode() === 1062) {
        ingredient_reply(409, [
            'success' => false, 'field' => 'name',
            'message' => 'An ingredient with this name already exists.',
        ]);
    }
    ingredient_reply(500, ['success' => false, 'message' => 'Unable to complete database operation.']);
} catch (Throwable $error) {
    error_log('Ingredient API error: ' . $error->getMessage());
    ingredient_reply(500, ['success' => false, 'message' => 'Unable to process ingredient request.']);
}
