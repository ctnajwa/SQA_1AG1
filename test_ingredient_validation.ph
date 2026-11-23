<?php
declare(strict_types=1);

// Local CLI check only. Does NOT require a working database and does NOT claim
// that TC-IS-01, FC-02 integration, or all SQAD tactics are verified.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
require_once __DIR__ . '/ingredient_validation.php';

$valid = [
    'name' => 'Plain Flour Test',
    'category' => 'Baking',
    'quantity' => 10,
    'unit' => 'KG',
    'expiry' => '2026-12-31',
];
$tests = [
    ['Valid ingredient', $valid, true],
    ['Missing name', array_replace($valid, ['name' => '']), false],
    ['Negative quantity', array_replace($valid, ['quantity' => -1]), false],
    ['Too many decimals', array_replace($valid, ['quantity' => 1.234]), false],
    ['Bad category', array_replace($valid, ['category' => 'Baking Ingredient']), false],
    ['Bad unit', array_replace($valid, ['unit' => 'BOX']), false],
    ['Invalid calendar date', array_replace($valid, ['expiry' => '2026-02-30']), false],
    ['Missing expiry', array_replace($valid, ['expiry' => '']), false],
];
$passed = 0;
foreach ($tests as [$name, $payload, $shouldPass]) {
    try {
        validate_ingredient_payload($payload);
        $actual = true;
    } catch (IngredientValidationError $error) {
        $actual = false;
    }
    if ($actual === $shouldPass) {
        echo "[PASS] " . $name . "\n";
        $passed++;
    } else {
        echo "[FAIL] " . $name . "\n";
    }
}
echo "Total: {$passed}/" . count($tests) . "\n";
exit($passed === count($tests) ? 0 : 1);
