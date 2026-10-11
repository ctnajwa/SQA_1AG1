```php
<?php
include "db.php";

// Only allow deletion through the confirmation form
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: supplier.php");
    exit();
}

// Validate supplier ID
if (
    !isset($_POST['supplier_id']) ||
    !filter_var($_POST['supplier_id'], FILTER_VALIDATE_INT)
) {
    header("Location: supplier.php");
    exit();
}

$id = (int) $_POST['supplier_id'];

// Delete the confirmed supplier
$sql = "DELETE FROM supplier WHERE supplier_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute() && $stmt->affected_rows === 1) {
    $message = "Supplier deleted successfully!";
    $success = true;
} else {
    $message = "Supplier could not be deleted. It may no longer exist.";
    $success = false;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SweetBites - Delete Result</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="dashboard">

    <aside class="sidebar">
        <div class="logo">
            <div class="logo-icon">🍰</div>

            <div>
                <h2>SweetBites</h2>
                <p>Inventory System</p>
            </div>
        </div>

        <nav>
            <a href="#" class="nav-item">
                <span>⌂</span> Dashboard
            </a>

            <a href="#" class="nav-item">
                <span>🥣</span> Ingredient Stock
            </a>

            <a href="supplier.php" class="nav-item active">
                <span>🚚</span> Supplier
            </a>

            <a href="#" class="nav-item">
                <span>📋</span> Purchasing Order
            </a>

            <a href="#" class="nav-item">
                <span>🎂</span> Product Catalog
            </a>
        </nav>
    </aside>


    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Delete Supplier</h1>
                <p>SweetBites inventory management</p>
            </div>

            <div class="admin-profile">
                <div class="profile-circle">A</div>

                <div>
                    <strong>Administrator</strong>
                    <small>Admin</small>
                </div>
            </div>
        </header>


        <section class="content">

            <div class="page-header">
                <div>
                    <h2>Deletion Result</h2>
                    <p>Supplier record status</p>
                </div>
            </div>

            <div class="form-card result-card">

                <?php if ($success) { ?>

                    <div class="result-icon success-icon">✓</div>

                    <h2 class="result-title">
                        Supplier Deleted!
                    </h2>

                <?php } else { ?>

                    <div class="result-icon error-icon">!</div>

                    <h2 class="result-title">
                        Deletion Failed
                    </h2>

                <?php } ?>

                <p class="result-message">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </p>

                <div class="result-actions">
                    <a href="supplier.php" class="save-button">
                        Back to Supplier List
                    </a>
                </div>

            </div>

        </section>

    </main>

</div>

</body>
</html>
```