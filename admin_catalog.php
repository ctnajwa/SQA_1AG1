<?php
include "db.php";

$toastMessage = isset($_GET["updated"]) && $_GET["updated"] === "1"
    ? "Cake Information Successfully Updated"
    : "";

// Handle POST delete request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $deleteId = filter_input(INPUT_POST, "delete_id", FILTER_VALIDATE_INT);

    if ($deleteId) {
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $deleteId);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $toastMessage = "Cake has been deleted successfully";
            } else {
                $toastMessage = "Cake could not be deleted";
            }
            $stmt->close();
        }
    }
}

// Retrieve all products from cake_shop_inventory
$products = [];
$sql = "SELECT product_id, product_name, category, description, flavor, size, price, availability, stock_quantity, image_path FROM products ORDER BY product_id DESC";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $result->free();
}

$conn->close();

function getAvailabilityBadgeClass(string $availability): string {
    switch ($availability) {
        case "Available":
            return "badge-available";
        case "Out of Stock":
            return "badge-out";
        case "Pre-Order":
            return "badge-preorder";
        default:
            return "badge-available";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Product Catalog - SweetBites</title>
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
  }

  /* Page header */
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 22px;
    flex-wrap: wrap;
  }
  .page-header h1 {
    font-size: 22px;
    font-weight: 700;
    color: #4A1F33;
    margin-bottom: 4px;
  }
  .page-header p { font-size: 12.5px; color: #999; }

  /* ---------- Card ---------- */
  .card {
    background: white;
    border-radius: 10px;
    border: 1px solid #EEE;
    padding: 22px 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 18px;
    flex-wrap: wrap;
  }
  .card-header .title-block h2 {
    font-size: 15.5px;
    font-weight: 700;
    color: #4A1F33;
    margin-bottom: 3px;
  }
  .card-header .title-block p {
    font-size: 12px;
    color: #999;
  }

  /* ---------- Toolbar ---------- */
  .toolbar {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 18px;
    align-items: center;
  }
  .search-bar {
    position: relative;
    flex: 1;
    min-width: 220px;
    max-width: 380px;
  }
  .search-bar input {
    width: 100%;
    padding: 9px 14px;
    border: 1px solid #E5E5E5;
    border-radius: 6px;
    font-size: 12.5px;
    background: #FAFAFA;
    color: #333;
  }
  .search-bar input::placeholder { color: #B5B5B5; }
  .search-bar input:focus {
    outline: none;
    border-color: #6E2C4A;
    background: white;
    box-shadow: 0 0 0 3px rgba(110,44,74,0.1);
  }
  .filter-select {
    padding: 9px 14px;
    border: 1px solid #E5E5E5;
    border-radius: 6px;
    font-size: 12.5px;
    background: #FAFAFA;
    color: #555;
    cursor: pointer;
  }
  .filter-select:focus {
    outline: none;
    border-color: #6E2C4A;
    background: white;
    box-shadow: 0 0 0 3px rgba(110,44,74,0.1);
  }

  .btn-add {
    display: inline-block;
    background: #6E2C4A;
    color: white;
    border: none;
    padding: 9px 18px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
    margin-left: auto;
    text-decoration: none;
  }
  .btn-add:hover { background: #5A2139; }

  /* Result count */
  .result-count {
    font-size: 12.5px;
    color: #999;
    margin-bottom: 16px;
  }
  .result-count strong { color: #4A1F33; }

  /* ---------- Catalog Grid ---------- */
  .catalog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 18px;
  }

  /* ---------- Cake Card ---------- */
  .cake-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #EEE;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    transition: 0.25s;
    display: flex;
    flex-direction: column;
  }
  .cake-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(110,44,74,0.12);
    border-color: #EDE3E7;
  }

  /* Cake Image */
  .cake-image {
    height: 155px;
    background: linear-gradient(135deg, #F7F0F3, #EDE3E7);
    position: relative;
  }
  .cake-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .status-badge {
    position: absolute;
    top: 12px;
    right: 12px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    color: white;
    letter-spacing: 0.4px;
  }
  .badge-available { background: #4CAF50; }
  .badge-out { background: #E53935; }
  .badge-preorder { background: #FBC02D; color: #333; }

  /* Cake Body */
  .cake-body {
    padding: 16px;
    flex: 1;
    display: flex;
    flex-direction: column;
  }
  .cake-category {
    font-size: 10.5px;
    color: #6E2C4A;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    margin-bottom: 5px;
  }
  .cake-name {
    font-size: 14.5px;
    font-weight: 700;
    color: #4A1F33;
    margin-bottom: 6px;
  }
  .cake-desc {
    font-size: 12px;
    color: #888;
    line-height: 1.5;
    margin-bottom: 12px;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .cake-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 12px;
    border-top: 1px solid #F2F2F2;
  }
  .cake-price {
    font-size: 16px;
    font-weight: 700;
    color: #6E2C4A;
  }
  .cake-price small {
    font-size: 10px;
    color: #999;
    font-weight: 500;
    display: block;
    margin-top: 1px;
  }
  .cake-size {
    font-size: 10.5px;
    color: #6E2C4A;
    background: #F7F0F3;
    padding: 4px 10px;
    border-radius: 20px;
    border: 1px solid #EDE3E7;
    font-weight: 600;
  }

  /* Card Actions */
  .cake-actions {
    display: flex;
    gap: 6px;
    padding: 12px 16px;
    background: #FCFAFB;
    border-top: 1px solid #F2F2F2;
  }
  .cake-actions button,
  .cake-actions a {
    flex: 1;
    padding: 7px 8px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 5px;
    cursor: pointer;
    border: 1px solid transparent;
    background: #EDE3E7;
    color: #6E2C4A;
    transition: 0.2s;
    text-align: center;
    text-decoration: none;
  }
  .cake-actions button:hover,
  .cake-actions a:hover { background: #E0D0D8; }

  .empty-catalog {
    grid-column: 1 / -1;
    text-align: center;
    padding: 28px 16px;
    color: #888;
    font-size: 13px;
  }

  /* ---------- Detail View Modal ---------- */
  .modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(74,31,51,0.4);
    z-index: 100;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }
  .modal-overlay.active { display: flex; }

  .modal {
    background: white;
    border-radius: 12px;
    max-width: 560px;
    width: 100%;
    max-height: 90vh;
    overflow: auto;
    box-shadow: 0 20px 60px rgba(74,31,51,0.25);
    animation: popIn 0.2s ease;
  }
  @keyframes popIn {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
  }

  .modal-header {
    background: #F7F0F3;
    padding: 16px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 12px 12px 0 0;
    border-bottom: 1px solid #EDE3E7;
  }
  .modal-header h3 {
    font-size: 15px;
    font-weight: 700;
    color: #6E2C4A;
  }
  .modal-close {
    background: transparent;
    border: none;
    color: #6E2C4A;
    font-size: 22px;
    cursor: pointer;
    line-height: 1;
    transition: 0.2s;
    opacity: 0.7;
  }
  .modal-close:hover { opacity: 1; }

  .modal-body { padding: 20px 22px; }

  .modal-cake-image {
    height: 170px;
    background: linear-gradient(135deg, #F7F0F3, #EDE3E7);
    border-radius: 10px;
    margin-bottom: 18px;
    overflow: hidden;
  }
  .modal-cake-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .detail-row {
    display: flex;
    padding: 10px 0;
    border-bottom: 1px solid #F2F2F2;
    font-size: 12.5px;
    align-items: flex-start;
  }
  .detail-row:last-child { border-bottom: none; }
  .detail-label {
    width: 145px;
    color: #999;
    font-weight: 600;
    font-size: 12px;
    flex-shrink: 0;
  }
  .detail-value { color: #333; flex: 1; }
  .detail-value.price { color: #6E2C4A; font-weight: 700; font-size: 15px; }

  .modal-footer {
    padding: 14px 22px 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid #F2F2F2;
  }
  .btn-secondary {
    background: white;
    color: #6E2C4A;
    border: 1px solid #EDE3E7;
    padding: 9px 20px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
  }
  .btn-secondary:hover { background: #F7F0F3; }

  .btn-primary {
    background: #6E2C4A;
    color: white;
    border: none;
    padding: 9px 20px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
    text-decoration: none;
    display: inline-block;
  }
  .btn-primary:hover { background: #5A2139; }

  .toast {
    display: none;
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #222;
    color: white;
    padding: 12px 20px;
    border-radius: 7px;
    font-size: 13px;
    z-index: 110;
    box-shadow: 0 5px 18px rgba(0,0,0,0.25);
    max-width: calc(100% - 30px);
    text-align: center;
  }
  .toast.show { display: block; }

  @media (max-width: 900px) {
    .sidebar { display: none; }
    .main { padding: 20px; }
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

    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1>Product Catalog</h1>
      </div>
    </div>

    <!-- Card -->
    <div class="card">

      <!-- Card Header -->
      <div class="card-header">
        <div class="title-block">
          <h2>Cake List</h2>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="toolbar">
        <div class="search-bar">
          <input type="text" id="searchInput" placeholder="Search cakes by name, flavor, or category..." oninput="filterCatalog()">
        </div>
        <select class="filter-select" id="categoryFilter" onchange="filterCatalog()">
          <option value="all">All Categories</option>
          <option value="Birthday Cake">Birthday Cake</option>
          <option value="Wedding Cake">Wedding Cake</option>
          <option value="Cupcake">Cupcake</option>
          <option value="Cheesecake">Cheesecake</option>
          <option value="Custom Cake">Custom Cake</option>
        </select>
        <select class="filter-select" id="statusFilter" onchange="filterCatalog()">
          <option value="all">All Status</option>
          <option value="Available">Available</option>
          <option value="Out of Stock">Out of Stock</option>
          <option value="Pre-Order">Pre-Order</option>
        </select>
        <a class="btn-add" href="admin_create_cake.php">+ Add New Cake</a>
      </div>

      <div class="result-count">
        Showing <strong id="resultCount"><?php echo count($products); ?></strong> cakes
      </div>

      <!-- Catalog Grid -->
      <div class="catalog-grid" id="catalogGrid">
        <?php if (!empty($products)): ?>
          <?php foreach ($products as $cake): ?>
            <?php
              $id = (int) $cake["product_id"];
              $name = $cake["product_name"] ?? "";
              $category = $cake["category"] ?? "";
              $description = $cake["description"] ?? "";
              $flavor = $cake["flavor"] ?? "-";
              $size = $cake["size"] ?? "-";
              $price = number_format((float) ($cake["price"] ?? 0), 2, ".", "");
              $availability = $cake["availability"] ?? "Available";
              $stockQty = $cake["stock_quantity"] !== null ? (string) (int) $cake["stock_quantity"] : "N/A";
              $imagePath = !empty($cake["image_path"]) ? $cake["image_path"] : "Images/choco_fudge.jpg";
              $badgeClass = getAvailabilityBadgeClass($availability);
            ?>
            <div class="cake-card"
                 data-name="<?php echo htmlspecialchars(strtolower($name), ENT_QUOTES, 'UTF-8'); ?>"
                 data-flavor="<?php echo htmlspecialchars(strtolower($flavor), ENT_QUOTES, 'UTF-8'); ?>"
                 data-category="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>"
                 data-status="<?php echo htmlspecialchars($availability, ENT_QUOTES, 'UTF-8'); ?>"
                 data-id="<?php echo $id; ?>"
                 data-cake-name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                 data-cake-category="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>"
                 data-cake-flavor="<?php echo htmlspecialchars($flavor, ENT_QUOTES, 'UTF-8'); ?>"
                 data-cake-size="<?php echo htmlspecialchars($size, ENT_QUOTES, 'UTF-8'); ?>"
                 data-cake-desc="<?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>"
                 data-cake-status="<?php echo htmlspecialchars($availability, ENT_QUOTES, 'UTF-8'); ?>"
                 data-cake-badge="<?php echo htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8'); ?>"
                 data-cake-stock="<?php echo htmlspecialchars($stockQty, ENT_QUOTES, 'UTF-8'); ?>"
                 data-cake-price="<?php echo htmlspecialchars($price, ENT_QUOTES, 'UTF-8'); ?>"
                 data-cake-image="<?php echo htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8'); ?>">
              <div class="cake-image">
                <img src="<?php echo htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                <span class="status-badge <?php echo htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(strtoupper($availability), ENT_QUOTES, 'UTF-8'); ?></span>
              </div>
              <div class="cake-body">
                <div class="cake-category"><?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="cake-name"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="cake-desc"><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="cake-meta">
                  <div class="cake-price">RM <?php echo htmlspecialchars($price, ENT_QUOTES, 'UTF-8'); ?> <small>per cake</small></div>
                  <div class="cake-size"><?php echo htmlspecialchars($size, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
              </div>
              <div class="cake-actions">
                <button type="button" onclick="openModal(this)">View</button>
                <a href="admin_edit_catalog.php?id=<?php echo $id; ?>">Edit</a>
                <button type="button" onclick="openDeleteModal(this)">Delete</button>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="empty-catalog" id="emptyCatalog" style="display: <?php echo empty($products) ? 'block' : 'none'; ?>;">
        No cakes found in the catalog.
      </div>
    </div>

  </main>

</div>

<!-- ---------- Detail View Modal ---------- -->
<div class="modal-overlay" id="detailModal">
  <div class="modal">

    <div class="modal-header">
      <h3>Cake Details</h3>
      <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
    </div>

    <div class="modal-body">
      <div class="modal-cake-image">
        <img id="modalImage" src="Images/choco_fudge.jpg" alt="Cake Image">
      </div>

      <div class="detail-row">
        <div class="detail-label">Cake Name</div>
        <div class="detail-value" id="modalName">-</div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Category</div>
        <div class="detail-value" id="modalCategory">-</div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Flavor</div>
        <div class="detail-value" id="modalFlavor">-</div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Size</div>
        <div class="detail-value" id="modalSize">-</div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Description</div>
        <div class="detail-value" id="modalDesc">-</div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Availability</div>
        <div class="detail-value">
          <span class="status-badge badge-available" id="modalBadge" style="position:static; display:inline-block;">AVAILABLE</span>
        </div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Stock Quantity</div>
        <div class="detail-value" id="modalStock">-</div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Price</div>
        <div class="detail-value price" id="modalPrice">RM 0.00</div>
      </div>
    </div>

    <div class="modal-footer">
      <button type="button" class="btn-secondary" onclick="closeModal()">Close</button>
      <a href="admin_edit_catalog.php" class="btn-primary" id="modalEditLink">Edit Cake</a>
    </div>

  </div>
</div>

<!-- ---------- Delete Confirmation Modal ---------- -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal" style="max-width: 400px; text-align: center;">
    <form method="POST" action="admin_catalog.php" id="deleteForm">
      <input type="hidden" name="delete_id" id="deleteCakeId" value="">
      <div class="modal-body" style="padding: 30px 24px;">
        <p style="font-size: 14.5px; font-weight: 600; color: #333; margin-bottom: 24px; line-height: 1.5;" id="deleteMessage">
          Are you sure to delete this cake from the Catalog?
        </p>
        <div style="display: flex; gap: 10px; flex-direction: column;">
          <button type="submit" style="background: #ac1816; color: white; border: none; padding: 10px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 12.5px;">Delete</button>
          <button type="button" onclick="closeDeleteModal()" style="background: #888; color: white; border: none; padding: 10px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 12.5px;">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
  let toastTimer;

  function openModal(button) {
    const card = button.closest('.cake-card');
    if (!card) return;

    const name = card.dataset.cakeName || '-';
    const category = card.dataset.cakeCategory || '-';
    const flavor = card.dataset.cakeFlavor || '-';
    const size = card.dataset.cakeSize || '-';
    const desc = card.dataset.cakeDesc || '-';
    const status = card.dataset.cakeStatus || 'Available';
    const badgeClass = card.dataset.cakeBadge || 'badge-available';
    const stock = card.dataset.cakeStock || 'N/A';
    const price = card.dataset.cakePrice || '0.00';
    const image = card.dataset.cakeImage || 'Images/choco_fudge.jpg';
    const id = card.dataset.id || '';

    document.getElementById('modalImage').src = image;
    document.getElementById('modalImage').alt = name;
    document.getElementById('modalName').textContent = name;
    document.getElementById('modalCategory').textContent = category;
    document.getElementById('modalFlavor').textContent = flavor;
    document.getElementById('modalSize').textContent = size;
    document.getElementById('modalDesc').textContent = desc;
    document.getElementById('modalStock').textContent = stock;
    document.getElementById('modalPrice').textContent = `RM ${price}`;
    document.getElementById('modalEditLink').href = id ? `admin_edit_catalog.php?id=${encodeURIComponent(id)}` : 'admin_edit_catalog.php';

    const badgeEl = document.getElementById('modalBadge');
    badgeEl.className = `status-badge ${badgeClass}`;
    badgeEl.textContent = status.toUpperCase();

    document.getElementById('detailModal').classList.add('active');
  }

  function closeModal() {
    document.getElementById('detailModal').classList.remove('active');
  }

  document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
  });

  function openDeleteModal(button) {
    const card = button.closest('.cake-card');
    if (!card) return;

    const cakeId = card.dataset.id || '';
    const cakeName = card.dataset.cakeName || card.querySelector('.cake-name').innerText;

    document.getElementById('deleteCakeId').value = cakeId;
    document.getElementById('deleteMessage').innerHTML = `Are you sure to delete<br><strong>${escapeHtml(cakeName)}</strong><br>from the Product Catalog?`;
    document.getElementById('deleteModal').classList.add('active');
  }

  function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    document.getElementById('deleteCakeId').value = '';
  }

  document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
  });

  function filterCatalog() {
    const query = document.getElementById('searchInput').value.toLowerCase().trim();
    const category = document.getElementById('categoryFilter').value;
    const status = document.getElementById('statusFilter').value;

    const cards = document.querySelectorAll('#catalogGrid .cake-card');
    let visibleCount = 0;

    cards.forEach(card => {
      const name = (card.dataset.name || '').toLowerCase();
      const flavor = (card.dataset.flavor || '').toLowerCase();
      const cardCat = card.dataset.category || '';
      const cardStatus = card.dataset.status || '';

      const matchesSearch = !query || name.includes(query) || flavor.includes(query) || cardCat.toLowerCase().includes(query);
      const matchesCat = category === 'all' || cardCat === category;
      const matchesStatus = status === 'all' || cardStatus === status;

      const show = matchesSearch && matchesCat && matchesStatus;
      card.style.display = show ? 'flex' : 'none';
      if (show) visibleCount++;
    });

    document.getElementById('resultCount').textContent = visibleCount;
    document.getElementById('emptyCatalog').style.display = visibleCount === 0 ? 'block' : 'none';
  }

  function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, s => ({
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#39;"
    }[s]));
  }

  function showToast(message) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.classList.add('show');

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      toast.classList.remove('show');
    }, 2500);
  }

  <?php if ($toastMessage !== ""): ?>
    showToast(<?php echo json_encode($toastMessage); ?>);
  <?php endif; ?>
</script>

</body>
</html>