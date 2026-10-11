```php
<?php
include "db.php";

// Get the supplier ID from the URL
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: supplier.php");
    exit();
}

$id = (int) $_GET['id'];

// Retrieve the selected supplier
$sql = "SELECT * FROM supplier WHERE supplier_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$supplier = $result->fetch_assoc();

$stmt->close();

// Redirect if the supplier does not exist
if (!$supplier) {
    $conn->close();
    header("Location: supplier.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SweetBites - Edit Supplier</title>

    <!-- Connect the CSS file -->
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
                <span>⌂</span>
                Dashboard
            </a>

            <a href="#" class="nav-item">
                <span>🥣</span>
                Ingredient Stock
            </a>

            <a href="supplier.php" class="nav-item active">
                <span>🚚</span>
                Supplier
            </a>

            <a href="#" class="nav-item">
                <span>📋</span>
                Purchasing Order
            </a>

            <a href="#" class="nav-item">
                <span>🎂</span>
                Product Catalog
            </a>
        </nav>

    </aside>


    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- TOP BAR -->
        <header class="topbar">

            <div>
                <h1>Edit Supplier</h1>
                <p>Update your SweetBites supplier information</p>
            </div>

            <div class="admin-profile">
                <div class="profile-circle">A</div>

                <div>
                    <strong>Administrator</strong>
                    <small>Admin</small>
                </div>
            </div>

        </header>


        <!-- PAGE CONTENT -->
        <section class="content">

            <div class="page-header">
                <div>
                    <h2>Edit Supplier Details</h2>
                    <p>Make changes to the supplier information below.</p>
                </div>
            </div>


            <!-- EDIT SUPPLIER FORM -->
            <div class="form-card">

                <form action="update_supplier.php" method="POST">

                    <input
                        type="hidden"
                        name="supplier_id"
                        value="<?php echo (int) $supplier['supplier_id']; ?>"
                    >

                    <!-- SUPPLIER NAME -->
                    <div class="form-group">
                        <label for="supplier_name">Supplier Name *</label>

                        <input
                            type="text"
                            id="supplier_name"
                            name="supplier_name"
                            value="<?php echo htmlspecialchars($supplier['supplier_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="Enter supplier name"
                            maxlength="100"
                            required
                        >
                    </div>


                    <!-- CONTACT PERSON -->
                    <div class="form-group">
                        <label for="contact_person">Contact Person</label>

                        <input
                            type="text"
                            id="contact_person"
                            name="contact_person"
                            value="<?php echo htmlspecialchars($supplier['contact_person'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="Enter contact person's name"
                            maxlength="100"
                        >
                    </div>


                    <!-- PHONE -->
                    <div class="form-group">
                        <label for="phone">Phone Number</label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="<?php echo htmlspecialchars($supplier['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="Enter phone number"
                            maxlength="20"
                        >
                    </div>


                    <!-- EMAIL -->
                    <div class="form-group">
                        <label for="email">Email Address</label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($supplier['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            placeholder="Enter email address"
                            maxlength="100"
                        >
                    </div>


                    <!-- ADDRESS -->
                    <div class="form-group">
                        <label for="address">Address</label>

                        <textarea
                            id="address"
                            name="address"
                            rows="4"
                            placeholder="Enter supplier address"
                        ><?php echo htmlspecialchars($supplier['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>


                    <!-- STATUS -->
                    <div class="form-group">
                        <label for="status">Supplier Status *</label>

                        <select id="status" name="status" required>
                            <option value="Active"
                                <?php echo (($supplier['status'] ?? '') === 'Active') ? 'selected' : ''; ?>>
                                Active
                            </option>

                            <option value="Inactive"
                                <?php echo (($supplier['status'] ?? '') === 'Inactive') ? 'selected' : ''; ?>>
                                Inactive
                            </option>
                        </select>
                    </div>


                    <!-- FORM BUTTONS -->
                    <div class="form-buttons">

                        <a href="supplier.php" class="cancel-button">
                            Cancel
                        </a>

                        <button type="submit" class="save-button">
                            Update Supplier
                        </button>

                    </div>

                </form>

            </div>

        </section>

    </main>

</div>

</body>
</html>
```