```php
<?php
include "db.php";

// Get supplier ID
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: supplier.php");
    exit();
}

$id = (int) $_GET['id'];

// Find the supplier
$sql = "SELECT supplier_id, supplier_name FROM supplier WHERE supplier_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$supplier = $result->fetch_assoc();

$stmt->close();

// Redirect if supplier does not exist
if (!$supplier) {
    $conn->close();
    header("Location: supplier.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SweetBites - Delete Supplier</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->
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


    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- TOP BAR -->
        <header class="topbar">
            <div>
                <h1>Delete Supplier</h1>
                <p>Manage your SweetBites suppliers</p>
            </div>

            <div class="admin-profile">
                <div class="profile-circle">A</div>

                <div>
                    <strong>Administrator</strong>
                    <small>Admin</small>
                </div>
            </div>
        </header>


        <!-- CONFIRMATION CONTENT -->
        <section class="content">

            <div class="page-header">
                <div>
                    <h2>Confirm Deletion</h2>
                    <p>Please review your action before continuing.</p>
                </div>
            </div>

            <div class="form-card delete-confirm-card">

                <div class="delete-warning-icon">!</div>

                <h2>Are you sure?</h2>

                <p class="delete-message">
                    Do you really want to delete this supplier?
                    This action cannot be undone.
                </p>

                <div class="supplier-to-delete">
                    <span>Supplier Name</span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $supplier['supplier_name'],
                            ENT_QUOTES,
                            'UTF-8'
                        );
                        ?>
                    </strong>
                </div>

                <!-- YES: Submit deletion -->
                <form action="confirm_delete_supplier.php"
                      method="POST"
                      class="delete-confirm-actions">

                    <input type="hidden"
                           name="supplier_id"
                           value="<?php echo (int) $supplier['supplier_id']; ?>">

                    <button type="submit" class="delete-yes-button">
                        Yes, Delete
                    </button>

                    <!-- NO: Return without deleting -->
                    <a href="supplier.php" class="delete-no-button">
                        No, Cancel
                    </a>

                </form>

            </div>

        </section>

    </main>

</div>

</body>
</html>
```