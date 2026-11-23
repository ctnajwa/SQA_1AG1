<?php
declare(strict_types=1);

/**
 * Shared server-side validation for Add and Update requests.
 * FC-T01 (shared rules), FC-T03 (server-side enforcement).
 */
final class IngredientValidationError extends InvalidArgumentException
{
    public string $field;

    public function __construct(string $field, string $message)
    {
        parent::__construct($message);
        $this->field = $field;
    }
}

function validate_ingredient_payload(array $data): array
{
    $name = $data['name'] ?? null;
    if (!is_string($name) || trim($name) === '' || strlen(trim($name)) > 100) {
        throw new IngredientValidationError(
            'name', 'Ingredient Name is required and must be at most 100 characters.'
        );
    }
    $name = trim($name);

    $category = $data['category'] ?? null;
    $allowedCategories = ['Baking', 'Dairy', 'Fruits', 'Sweeteners', 'Others'];
    if (!is_string($category) || !in_array($category, $allowedCategories, true)) {
        throw new IngredientValidationError('category', 'Please select a valid ingredient category.');
    }

    $rawQuantity = $data['quantity'] ?? null;
    if ((!is_string($rawQuantity) && !is_int($rawQuantity) && !is_float($rawQuantity))
        || !preg_match('/^(?:0|[1-9][0-9]{0,7})(?:\.[0-9]{1,2})?$/D', (string)$rawQuantity)) {
        throw new IngredientValidationError(
            'quantity', 'Quantity must be between 0 and 99,999,999.99 with up to two decimals.'
        );
    }
    $quantity = (float)$rawQuantity;

    $unit = $data['unit'] ?? null;
    if (!is_string($unit) || !in_array($unit, ['KG', 'G', 'PCS', 'L', 'ML'], true)) {
        throw new IngredientValidationError('unit', 'Please select a valid unit.');
    }

    $expiry = $data['expiry'] ?? null;
    if (!is_string($expiry) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $expiry)) {
        throw new IngredientValidationError('expiry', 'Expiry Date is required in YYYY-MM-DD format.');
    }
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $expiry);
    if ($date === false || $date->format('Y-m-d') !== $expiry) {
        throw new IngredientValidationError('expiry', 'Please enter a valid calendar date.');
    }

    return compact('name', 'category', 'quantity', 'unit', 'expiry');
}
