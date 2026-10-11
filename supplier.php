```php
<?php
include "db.php";

// Retrieve all suppliers
$sql = "SELECT * FROM supplier ORDER BY supplier_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SweetBites - Manage Supplier</title>

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
                <h1>Supplier</h1>
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


        <!-- PAGE CONTENT -->
        <section class="content">

            <div class="page-header">

                <div>
                    <h2>Supplier List</h2>
                    <p>View and manage registered suppliers</p>
                </div>

                <a href="add_supplier.php" class="add-button">
                    + Add New Supplier
                </a>

            </div>


            <!-- SEARCH BAR -->
            <div class="search-container">

                <input
                    type="text"
                    id="searchInput"
                    class="search-input"
                    placeholder="Search supplier..."
                    onkeyup="searchSupplier()"
                >

            </div>


            <!-- SUPPLIER CARDS -->
            <div class="supplier-grid">

                <?php if ($result && $result->num_rows > 0) { ?>

                    <?php while ($row = $result->fetch_assoc()) { ?>

                        <div class="supplier-card">

                            <!-- SUPPLIER HEADER -->
                            <div class="card-header">

                                <div class="supplier-avatar">
                                    <?php
                                    echo strtoupper(
                                        substr($row['supplier_name'], 0, 1)
                                    );
                                    ?>
                                </div>

                                <div class="card-header-info">

                                    <h3 class="supplier-name">
                                        <?php
                                        echo htmlspecialchars(
                                            $row['supplier_name'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>
                                    </h3>

                                    <span class="status-badge <?php
                                        echo strtolower($row['status']) === 'active'
                                            ? 'active'
                                            : 'inactive';
                                    ?>">
                                        <?php
                                        echo htmlspecialchars(
                                            $row['status'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>
                                    </span>

                                </div>

                            </div>


                            <!-- SUPPLIER DETAILS -->
                            <div class="supplier-details">

                                <div class="supplier-detail">

                                    <span class="detail-icon">👤</span>

                                    <div>
                                        <span class="detail-label">
                                            Contact Person
                                        </span>

                                        <span class="detail-value">
                                            <?php
                                            echo htmlspecialchars(
                                                $row['contact_person'] ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </span>
                                    </div>

                                </div>


                                <div class="supplier-detail">

                                    <span class="detail-icon">📞</span>

                                    <div>
                                        <span class="detail-label">
                                            Phone
                                        </span>

                                        <span class="detail-value">
                                            <?php
                                            echo htmlspecialchars(
                                                $row['phone'] ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </span>
                                    </div>

                                </div>


                                <div class="supplier-detail">

                                    <span class="detail-icon">✉</span>

                                    <div>
                                        <span class="detail-label">
                                            Email
                                        </span>

                                        <span class="detail-value">
                                            <?php
                                            echo htmlspecialchars(
                                                $row['email'] ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </span>
                                    </div>

                                </div>


                                <div class="supplier-detail">

                                    <span class="detail-icon">📍</span>

                                    <div>
                                        <span class="detail-label">
                                            Address
                                        </span>

                                        <span class="detail-value">
                                            <?php
                                            echo htmlspecialchars(
                                                $row['address'] ?? '',
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </span>
                                    </div>

                                </div>

                            </div>


                            <!-- ACTION BUTTONS -->
                            <div class="card-actions">

                                <a
                                    href="edit_supplier.php?id=<?php
                                        echo (int) $row['supplier_id'];
                                    ?>"
                                    class="edit-button"
                                >
                                    Edit
                                </a>


                                <!-- No JavaScript confirm popup here -->
                                <a
                                    href="delete_supplier.php?id=<?php
                                        echo (int) $row['supplier_id'];
                                    ?>"
                                    class="delete-button"
                                >
                                    Delete
                                </a>

                            </div>

                        </div>

                    <?php } ?>

                <?php } else { ?>

                    <div class="empty-state">

                        <div class="empty-state-icon">🚚</div>

                        <h3>No Suppliers Found</h3>

                        <p>
                            You haven't added any suppliers yet.
                            Click below to add your first supplier.
                        </p>

                        <a href="add_supplier.php" class="add-button">
                            + Add New Supplier
                        </a>

                    </div>

                <?php } ?>

            </div>

        </section>

    </main>

</div>


<!-- SEARCH FUNCTION -->
<script>
function searchSupplier() {
    const input = document
        .getElementById("searchInput")
        .value
        .toLowerCase();

    const cards = document.querySelectorAll(".supplier-card");

    cards.forEach(function(card) {
        const supplierText = card.innerText.toLowerCase();

        card.style.display = supplierText.includes(input)
            ? "flex"
            : "none";
    });
}
</script>

</body>
</html>

<?php
$conn->close();
?>
```