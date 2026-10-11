
<?php

include "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST['supplier_id'];
    $supplier_name = trim($_POST['supplier_name']);
    $contact_person = trim($_POST['contact_person']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $status = $_POST['status'];

    $sql = "UPDATE supplier SET
            supplier_name = ?,
            contact_person = ?,
            phone = ?,
            email = ?,
            address = ?,
            status = ?
            WHERE supplier_id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ssssssi",
        $supplier_name,
        $contact_person,
        $phone,
        $email,
        $address,
        $status,
        $id
    );

    if ($stmt->execute()) {

        if ($stmt->affected_rows >= 0) {
            $success = true;
        } else {
            $success = false;
        }

    } else {
        $success = false;
    }

    $stmt->close();

} else {
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

    <title>SweetBites - Update Supplier</title>

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

            <header class="topbar">

                <div>
                    <h1>Update Supplier</h1>
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
                        <h2>Update Result</h2>
                        <p>Supplier record update status</p>
                    </div>
                </div>


                <div class="form-card result-card">

                    <?php if ($success) { ?>

                        <div class="result-icon success-icon">✓</div>

                        <h2 class="result-title">
                            Supplier Updated Successfully!
                        </h2>

                        <p class="result-message">
                            The supplier information has been saved.
                        </p>

                    <?php } else { ?>

                        <div class="result-icon error-icon">!</div>

                        <h2 class="result-title">
                            Update Failed
                        </h2>

                        <p class="result-message">
                            We couldn't update the supplier information.
                            Please return to the list and try again.
                        </p>

                    <?php } ?>


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
