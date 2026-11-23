<?php
declare(strict_types=1);

/** Defined route and explicit failure handling for the Ingredient -> Purchase Order boundary. */
require_once __DIR__ . '/ingredient_repository.php';

function ingredient_route_error(int $status, string $message): void
{
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    $safe = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    echo '<!doctype html><html lang="en"><meta charset="utf-8">';
    echo '<title>Purchase Order Unavailable</title><body>';
    echo '<h1>Unable to open Purchase Order</h1><p>' . $safe . '</p>';
    echo '<p><a href="admin_ingredient.php">Return to Ingredient Stock</a></p>';
    echo '</body></html>';
    exit;
}

$id = filter_var($_GET['ingredient_id'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
if ($id === false) {
    ingredient_route_error(400, 'Please select a valid ingredient.');
}

$destination = __DIR__ . '/admin_create_purchase_order.html';
if (!is_file($destination)) {
    ingredient_route_error(503, 'The Purchase Order page is currently unavailable.');
}

try {
    require __DIR__ . '/db.php';
    $item = ingredient_find($conn, (int)$id);
    if (!$item) {
        ingredient_route_error(404, 'The selected ingredient no longer exists.');
    }
    $today = new DateTimeImmutable('today');
    $expired = $item['expiry'] !== '' && $item['expiry'] < $today->format('Y-m-d');
    if ($expired || $item['quantity'] > 10) {
        ingredient_route_error(400, 'The selected ingredient is not currently classified as Low Stock.');
    }

    // The external Purchase Order form currently has no confirmed ingredient-ID contract.
    // Redirect only to the agreed page. Do not invent parameter support.
    header('Location: admin_create_purchase_order.html', true, 302);
    exit;
} catch (Throwable $error) {
    error_log('Ingredient purchase-order navigation error: ' . $error->getMessage());
    ingredient_route_error(503, 'The purchasing page could not be opened at this time.');
}
