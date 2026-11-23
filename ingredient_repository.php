<?php
declare(strict_types=1);

/**
 * A single, reusable MySQL data-access component for the Ingredient module.
 * P-T01: a bounded list query rather than one query per row.
 * P-T04: list/read/save use the same repository and row format.
 */
function ingredient_map_row(array $row): array
{
    return [
        'id' => (int)$row['id'],
        'name' => (string)$row['name'],
        'category' => (string)$row['category'],
        'quantity' => (float)$row['quantity'],
        'unit' => (string)$row['unit'],
        'expiry' => $row['expiry'] === null ? '' : (string)$row['expiry'],
    ];
}

function ingredient_list(mysqli $conn): array
{
    $sql = 'SELECT ingredient_id AS id, ingredient_name AS name, category, '
         . 'quantity, unit, expiry_date AS expiry '
         . 'FROM ingredients ORDER BY ingredient_name';
    $result = $conn->query($sql);
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = ingredient_map_row($row);
    }
    $result->free();
    return $rows;
}

function ingredient_find(mysqli $conn, int $id): ?array
{
    $stmt = $conn->prepare(
        'SELECT ingredient_id AS id, ingredient_name AS name, category, '
        . 'quantity, unit, expiry_date AS expiry '
        . 'FROM ingredients WHERE ingredient_id = ? LIMIT 1'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result(); // mysqlnd is included in standard XAMPP distributions.
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? ingredient_map_row($row) : null;
}

function ingredient_save(mysqli $conn, ?int $id, array $value): ?array
{
    $name = $value['name'];
    $category = $value['category'];
    $quantity = $value['quantity'];
    $unit = $value['unit'];
    $expiry = $value['expiry'];

    if ($id === null) {
        $stmt = $conn->prepare(
            'INSERT INTO ingredients '
            . '(ingredient_name, category, quantity, unit, expiry_date) '
            . 'VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ssdss', $name, $category, $quantity, $unit, $expiry);
        $stmt->execute();  // If it fails, no success response is emitted.
        $id = (int)$conn->insert_id;
        $stmt->close();
    } else {
        $stmt = $conn->prepare(
            'UPDATE ingredients SET ingredient_name = ?, category = ?, '
            . 'quantity = ?, unit = ?, expiry_date = ? WHERE ingredient_id = ?'
        );
        $stmt->bind_param('ssdssi', $name, $category, $quantity, $unit, $expiry, $id);
        $stmt->execute();
        $stmt->close();
    }

    // Read back the stored row; avoids reporting an update for a nonexistent ID.
    return ingredient_find($conn, $id);
}

function ingredient_delete(mysqli $conn, int $id): bool
{
    $stmt = $conn->prepare('DELETE FROM ingredients WHERE ingredient_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $deleted = $stmt->affected_rows > 0;
    $stmt->close();
    return $deleted;
}
