<?php
include "db.php";

$errors = [];

$cakeName = "";
$category = "";
$description = "";
$flavor = "";
$size = "Medium";
$availability = "Available";
$price = "";
$stockQuantity = "";
$discount = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cakeName = trim($_POST["product_name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $flavor = trim($_POST["flavor"] ?? "");
    $size = trim($_POST["size"] ?? "");
    $availability = trim($_POST["availability"] ?? "Available");
    $priceRaw = trim($_POST["price"] ?? "");
    $stockRaw = trim($_POST["stock_quantity"] ?? "");
    $discountRaw = trim($_POST["discount"] ?? "");

    $allowedCategories = ["Birthday Cake", "Wedding Cake", "Cupcake", "Custom Cake", "Cheesecake"];
    $allowedSizes = ["Small", "Medium", "Large", "Custom"];
    $allowedStatuses = ["Available", "Out of Stock", "Pre-Order"];

    if ($cakeName === "") {
        $errors[] = "Cake Name is required.";
    }
    if ($category === "" || !in_array($category, $allowedCategories, true)) {
        $errors[] = "Please select a valid Category.";
    }
    if ($description === "") {
        $errors[] = "Description is required.";
    }
    if ($flavor === "") {
        $errors[] = "Flavor is required.";
    }
    if ($size === "" || !in_array($size, $allowedSizes, true)) {
        $errors[] = "Please select a Size.";
    }
    if (!in_array($availability, $allowedStatuses, true)) {
        $errors[] = "Please select a valid Availability Status.";
    }

    if ($priceRaw === "" || !is_numeric($priceRaw) || (float) $priceRaw < 0) {
        $errors[] = "Please enter a valid Price (RM) of 0 or greater.";
    } else {
        $price = number_format((float) $priceRaw, 2, ".", "");
    }

    if ($stockRaw === "" || filter_var($stockRaw, FILTER_VALIDATE_INT) === false || (int) $stockRaw < 0) {
        $errors[] = "Please enter a valid Stock Quantity of 0 or greater.";
    } else {
        $stockQuantity = (int) $stockRaw;
    }

    if ($discountRaw !== "") {
        if (!is_numeric($discountRaw) || (float) $discountRaw < 0 || (float) $discountRaw > 100) {
            $errors[] = "Discount (%) must be between 0 and 100.";
        } else {
            $discount = (float) $discountRaw;
            if ($discount > 0 && empty($errors)) {
                $discountedPrice = (float) $price * (1 - ($discount / 100));
                $price = number_format(max(0, $discountedPrice), 2, ".", "");
            }
        }
    }

    // Handle optional image upload
    $imagePath = "Images/choco_fudge.jpg";
    if (isset($_FILES["cake_image"]) && $_FILES["cake_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES["cake_image"]["error"] !== UPLOAD_ERR_OK) {
            $errors[] = "Failed to upload cake image.";
        } else {
            $maxBytes = 5 * 1024 * 1024; // 5MB
            if ($_FILES["cake_image"]["size"] > $maxBytes) {
                $errors[] = "Cake image must not exceed 5MB.";
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = $finfo ? finfo_file($finfo, $_FILES["cake_image"]["tmp_name"]) : "";
            if ($finfo) {
                finfo_close($finfo);
            }

            $allowedMime = [
                "image/jpeg" => "jpg",
                "image/png"  => "png",
            ];

            if (!isset($allowedMime[$mimeType])) {
                $errors[] = "Only JPG and PNG images are allowed.";
            }

            if (empty($errors)) {
                $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . "Images";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }

                $ext = $allowedMime[$mimeType];
                $fileName = "cake_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
                $destPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

                if (move_uploaded_file($_FILES["cake_image"]["tmp_name"], $destPath)) {
                    $imagePath = "Images/" . $fileName;
                } else {
                    $errors[] = "Could not save the uploaded image to the Images folder.";
                }
            }
        }
    }

    // Check for duplicate product_name before inserting
    if (empty($errors)) {
        $checkStmt = $conn->prepare("SELECT product_id FROM products WHERE product_name = ? LIMIT 1");
        if ($checkStmt) {
            $checkStmt->bind_param("s", $cakeName);
            $checkStmt->execute();
            $checkStmt->store_result();
            if ($checkStmt->num_rows > 0) {
                $errors[] = "A cake with this name already exists in the catalog.";
            }
            $checkStmt->close();
        }
    }

    // Insert into products table
    if (empty($errors)) {
        $priceDecimal = (float) $price;
        $stmt = $conn->prepare(
            "INSERT INTO products (product_name, category, description, flavor, size, price, availability, stock_quantity, image_path)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if ($stmt) {
            $stmt->bind_param(
                "sssssdsis",
                $cakeName,
                $category,
                $description,
                $flavor,
                $size,
                $priceDecimal,
                $availability,
                $stockQuantity,
                $imagePath
            );
            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header("Location: admin_catalog.php?created=1");
                exit();
            } else {
                $errors[] = "Database error while saving cake: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Database error preparing insert statement.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Cake - SweetBites</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
  body { background: #F5F5F5; color: #333; }

  /* ---------- Top Navigation ---------- */
  .navbar {
    background: white;
    padding: 12px 26px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #EEE;
    position: sticky;
    top: 0;
    z-index: 50;
  }
  .navbar .brand {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .navbar .brand .logo-box {
    width: 38px;
    height: 38px;
    background: #6E2C4A;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
  }
  .navbar .brand .brand-text .name {
    font-size: 15px;
    font-weight: 700;
    color: #4A1F33;
    line-height: 1.1;
  }
  .navbar .brand .brand-text .sub {
    font-size: 10.5px;
    color: #999;
    margin-top: 1px;
  }

  .navbar .user {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .navbar .user .avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #6E2C4A;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
  }
  .navbar .user .info {
    text-align: right;
    font-size: 12px;
    line-height: 1.3;
  }
  .navbar .user .info .role { color: #333; font-weight: 600; }
  .navbar .user .info .name { color: #999; }
  .user-menu { position: relative; }
  .user-menu > summary {
    list-style: none;
    cursor: pointer;
  }
  .user-menu > summary::-webkit-details-marker { display: none; }
  .user-menu-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    min-width: 150px;
    padding: 6px;
    background: white;
    border: 1px solid #EEE;
    border-radius: 8px;
    box-shadow: 0 5px 18px rgba(0,0,0,0.12);
    z-index: 60;
  }
  .user-menu-dropdown a {
    display: block;
    padding: 9px 12px;
    border-radius: 5px;
    color: #6E2C4A;
    font-size: 13px;
    text-decoration: none;
  }
  .user-menu-dropdown a:hover,
  .user-menu-dropdown a:focus { background: #F7F0F3; }

  /* ---------- Layout ---------- */
  .layout { display: flex; min-height: calc(100vh - 63px); }

  .sidebar {
    width: 190px;
    background: white;
    border-right: 1px solid #EEE;
    padding: 22px 0;
    flex-shrink: 0;
  }
  .sidebar a {
    display: block;
    padding: 12px 22px;
    font-size: 13.5px;
    color: #666;
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: 0.15s;
  }
  .sidebar a.active {
    color: #4A1F33;
    font-weight: 600;
    border-left-color: #6E2C4A;
    background: #F7F0F3;
  }
  .sidebar a:hover { background: #FAF5F7; }

  /* ---------- Main ---------- */
  .main {
    flex: 1;
    padding: 26px 32px 40px;
    max-width: 1200px;
  }

  /* Breadcrumb */
  .breadcrumb {
    font-size: 12.5px;
    color: #999;
    margin-bottom: 14px;
  }
  .breadcrumb a { color: #6E2C4A; text-decoration: none; }
  .breadcrumb a:hover { text-decoration: underline; }

  /* Page header */
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
  }
  .page-header h1 {
    font-size: 22px;
    font-weight: 700;
    color: #4A1F33;
    margin-bottom: 4px;
  }
  .page-header p { font-size: 12.5px; color: #999; }

  /* Alert Box */
  .alert-error {
    background: #FCE8E6;
    border: 1px solid #F5C2BE;
    color: #A51D24;
    border-radius: 8px;
    padding: 14px 18px;
    margin-bottom: 18px;
    font-size: 12.5px;
  }
  .alert-error ul {
    margin-left: 18px;
    margin-top: 4px;
  }

  /* ---------- Form Grid ---------- */
  .form-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
  }

  /* ---------- Card ---------- */
  .card {
    background: white;
    border-radius: 10px;
    border: 1px solid #EEE;
    padding: 22px 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
  }
  .card h2 {
    font-size: 15.5px;
    font-weight: 700;
    color: #4A1F33;
    margin-bottom: 4px;
  }
  .card .card-sub {
    font-size: 12px;
    color: #999;
    margin-bottom: 18px;
    padding-bottom: 14px;
    border-bottom: 1px solid #F2F2F2;
  }

  /* ---------- Form Fields ---------- */
  .form-group { margin-bottom: 16px; }
  .form-group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #666;
    margin-bottom: 6px;
  }
  .required { color: #E53935; }

  .form-group input[type="text"],
  .form-group input[type="number"],
  .form-group select,
  .form-group textarea {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #E5E5E5;
    border-radius: 6px;
    font-size: 13px;
    background: #FAFAFA;
    color: #333;
    transition: 0.2s;
    font-family: inherit;
  }
  .form-group input:focus,
  .form-group select:focus,
  .form-group textarea:focus {
    outline: none;
    border-color: #6E2C4A;
    background: white;
    box-shadow: 0 0 0 3px rgba(110,44,74,0.1);
  }
  .form-group textarea { resize: vertical; min-height: 80px; }
  .form-group input::placeholder,
  .form-group textarea::placeholder { color: #B5B5B5; }

  /* ---------- Radio ---------- */
  .radio-group {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    padding: 4px 0;
  }
  .radio-group label {
    font-weight: normal;
    font-size: 12.5px;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    margin-bottom: 0;
    color: #555;
  }
  .radio-group input[type="radio"] { accent-color: #6E2C4A; }

  /* ---------- Upload Box ---------- */
  .upload-box {
    display: block;
    border: 2px dashed #EDE3E7;
    border-radius: 8px;
    padding: 32px 20px;
    text-align: center;
    background: #FCFAFB;
    cursor: pointer;
    transition: 0.2s;
  }
  .upload-box:hover,
  .upload-box.dragover {
    border-color: #6E2C4A;
    background: #F7F0F3;
  }
  .upload-box .upload-title {
    font-weight: 700;
    color: #6E2C4A;
    font-size: 12.5px;
    margin-bottom: 4px;
  }
  .upload-box .upload-hint {
    font-size: 11px;
    color: #999;
  }
  .upload-file-name {
    margin-top: 8px;
    font-size: 11.5px;
    font-weight: 600;
    color: #4A1F33;
  }

  /* ---------- Availability ---------- */
  .availability-options {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }
  .availability-options label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: normal;
    font-size: 12.5px;
    padding: 9px 12px;
    border: 1px solid #EEE;
    border-radius: 6px;
    cursor: pointer;
    background: #FAFAFA;
    transition: 0.15s;
    color: #555;
    margin-bottom: 0;
  }
  .availability-options label:hover { background: #F7F0F3; border-color: #EDE3E7; }
  .availability-options input { accent-color: #6E2C4A; }
  .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
  .dot-green { background: #4CAF50; }
  .dot-red { background: #E53935; }
  .dot-yellow { background: #FBC02D; }

  /* ---------- Action Bar ---------- */
  .actions {
    grid-column: 1 / -1;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 16px;
    border-top: 1px solid #F2F2F2;
    margin-top: 8px;
    flex-wrap: wrap;
  }
  .btn {
    padding: 9px 20px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid transparent;
    transition: 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none;
  }
  .btn-cancel {
    background: white;
    color: #666;
    border-color: #DDD;
  }
  .btn-cancel:hover { background: #F5F5F5; border-color: #BBB; }
  .btn-save {
    background: #6E2C4A;
    color: white;
  }
  .btn-save:hover { background: #5A2139; }

  @media (max-width: 900px) {
    .form-container { grid-template-columns: 1fr; }
    .sidebar { display: none; }
    .main { padding: 20px; }
    .actions { flex-direction: column; align-items: stretch; }
    .actions .btn { justify-content: center; }
  }
</style>
</head>
<body>

<!-- Top Navigation -->
<div class="navbar">
  <div class="brand">
    <div class="logo-box">🎂</div>
    <div class="brand-text">
      <div class="name">SweetBites</div>
      <div class="sub">Inventory System</div>
    </div>
  </div>
  <div class="user-menu">
    <details>
      <summary class="user" aria-label="Open admin menu">
        <div class="avatar">A</div>
        <div class="info">
          <div class="role">Administrator</div>
          <div class="name">Admin</div>
        </div>
      </summary>
      <div class="user-menu-dropdown">
        <a href="login.php">Logout</a>
      </div>
    </details>
  </div>
</div>

<!-- Layout -->
<div class="layout">

  <!-- Sidebar -->
  <aside class="sidebar">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_ingredient.php">Ingredient Stock</a>
    <a href="supplier.php">Supplier</a>
    <a href="admin_purchase_orders.php">Purchase Order</a>
    <a href="admin_catalog.php" class="active">Product Catalog</a>
  </aside>

  <!-- Main Content -->
  <main class="main">

    <!-- Breadcrumb -->
    <div class="breadcrumb">
      <a href="admin_dashboard.php">Dashboard</a> &nbsp;&gt;&nbsp;
      <a href="admin_catalog.php">Product Catalog</a> &nbsp;&gt;&nbsp;
      <span style="color:#333;">Add New Cake</span>
    </div>

    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1>Add New Cake</h1>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert-error" role="alert">
        <strong>Please fix the following before saving:</strong>
        <ul>
          <?php foreach ($errors as $err): ?>
            <li><?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Form -->
    <form class="form-container" method="POST" action="admin_create_cake.php" enctype="multipart/form-data">

      <!-- LEFT: Cake Information -->
      <div class="card">
        <h2>Cake Information</h2>
        <br>

        <div class="form-group">
          <label for="product_name">Cake Name <span class="required">*</span></label>
          <input
            type="text"
            id="product_name"
            name="product_name"
            placeholder="e.g. Chocolate Fudge Cake"
            value="<?php echo htmlspecialchars($cakeName, ENT_QUOTES, 'UTF-8'); ?>"
            required
          >
        </div>

        <div class="form-group">
          <label for="category">Category <span class="required">*</span></label>
          <select id="category" name="category" required>
            <option value="">-- Select Category --</option>
            <?php foreach (["Birthday Cake", "Wedding Cake", "Cupcake", "Custom Cake", "Cheesecake"] as $catOption): ?>
              <option value="<?php echo htmlspecialchars($catOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $category === $catOption ? "selected" : ""; ?>>
                <?php echo htmlspecialchars($catOption, ENT_QUOTES, 'UTF-8'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="description">Description <span class="required">*</span></label>
          <textarea
            id="description"
            name="description"
            placeholder="Describe the cake ..."
            required
          ><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <div class="form-group">
          <label for="flavor">Flavor <span class="required">*</span></label>
          <input
            type="text"
            id="flavor"
            name="flavor"
            placeholder="e.g. Vanilla, Chocolate, Red Velvet"
            value="<?php echo htmlspecialchars($flavor, ENT_QUOTES, 'UTF-8'); ?>"
            required
          >
        </div>

        <div class="form-group">
          <label>Size <span class="required">*</span></label>
          <div class="radio-group">
            <?php foreach (["Small", "Medium", "Large", "Custom"] as $sizeOption): ?>
              <label>
                <input
                  type="radio"
                  name="size"
                  value="<?php echo htmlspecialchars($sizeOption, ENT_QUOTES, 'UTF-8'); ?>"
                  <?php echo $size === $sizeOption ? "checked" : ""; ?>
                  required
                >
                <?php echo htmlspecialchars($sizeOption, ENT_QUOTES, 'UTF-8'); ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label>Availability Status <span class="required">*</span></label>
          <div class="availability-options">
            <label>
              <input type="radio" name="availability" value="Available" <?php echo $availability === "Available" ? "checked" : ""; ?> required>
              <span class="dot dot-green"></span> Available
            </label>
            <label>
              <input type="radio" name="availability" value="Out of Stock" <?php echo $availability === "Out of Stock" ? "checked" : ""; ?>>
              <span class="dot dot-red"></span> Out of Stock
            </label>
            <label>
              <input type="radio" name="availability" value="Pre-Order" <?php echo $availability === "Pre-Order" ? "checked" : ""; ?>>
              <span class="dot dot-yellow"></span> Pre-Order
            </label>
          </div>
        </div>
      </div>

      <!-- RIGHT: Image + Pricing -->
      <div>
        <div class="card" style="margin-bottom:20px;">
          <h2>Cake Image</h2>
          <div class="card-sub">Upload a photo of the cake</div>
          <label class="upload-box" id="uploadBox" for="cake_image">
            <div class="upload-title">+ Upload Image</div>
            <div class="upload-hint">Drag &amp; drop or click to browse</div>
            <div class="upload-hint" style="margin-top:4px;">JPG, PNG &bull; Max 5MB</div>
            <div class="upload-file-name" id="uploadFileName"></div>
          </label>
          <input
            type="file"
            id="cake_image"
            name="cake_image"
            accept=".jpg,.jpeg,.png,image/jpeg,image/png"
            style="display:none;"
          >
        </div>

        <div class="card">
          <h2>Pricing &amp; Stock</h2>
          <br>

          <div class="form-group">
            <label for="price">Price (RM) <span class="required">*</span></label>
            <input
              type="number"
              id="price"
              name="price"
              placeholder="0.00"
              min="0"
              step="0.01"
              value="<?php echo htmlspecialchars($price, ENT_QUOTES, 'UTF-8'); ?>"
              required
            >
          </div>

          <div class="form-group">
            <label for="stock_quantity">Stock Quantity <span class="required">*</span></label>
            <input
              type="number"
              id="stock_quantity"
              name="stock_quantity"
              placeholder="0"
              min="0"
              value="<?php echo htmlspecialchars($stockQuantity, ENT_QUOTES, 'UTF-8'); ?>"
              required
            >
          </div>

          <div class="form-group" style="margin-bottom:0;">
            <label for="discount">Discount (%) <span style="color:#999; font-weight:400;">(Optional)</span></label>
            <input
              type="number"
              id="discount"
              name="discount"
              placeholder="0"
              min="0"
              max="100"
              step="0.01"
              value="<?php echo htmlspecialchars($discount, ENT_QUOTES, 'UTF-8'); ?>"
            >
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="actions">
        <a class="btn btn-cancel" href="admin_catalog.php">Cancel</a>
        <button type="submit" class="btn btn-save">Save Cake</button>
      </div>

    </form>
  </main>

</div>

<script>
  const fileInput = document.getElementById("cake_image");
  const uploadBox = document.getElementById("uploadBox");
  const fileNameLabel = document.getElementById("uploadFileName");

  fileInput.addEventListener("change", function () {
    if (this.files && this.files.length > 0) {
      fileNameLabel.textContent = "Selected: " + this.files[0].name;
    } else {
      fileNameLabel.textContent = "";
    }
  });

  ["dragenter", "dragover"].forEach(evtName => {
    uploadBox.addEventListener(evtName, function (e) {
      e.preventDefault();
      uploadBox.classList.add("dragover");
    });
  });

  ["dragleave", "drop"].forEach(evtName => {
    uploadBox.addEventListener(evtName, function (e) {
      e.preventDefault();
      uploadBox.classList.remove("dragover");
    });
  });

  uploadBox.addEventListener("drop", function (e) {
    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
      fileInput.files = e.dataTransfer.files;
      fileNameLabel.textContent = "Selected: " + e.dataTransfer.files[0].name;
    }
  });
</script>

</body>
</html>