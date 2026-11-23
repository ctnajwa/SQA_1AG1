<?php
include "db.php";

$products = [];
$sql = "SELECT product_id, product_name, category, description, flavor, size, price, availability, stock_quantity, image_path, created_at
        FROM products
        ORDER BY product_id DESC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $result->free();
}

$conn->close();

function getCustomerBadgeClass(string $availability): string {
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
<title>SweetBites - Our Cakes</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
  body { background: #FDFAFB; color: #333; }

  /* ---------- Top Navigation (Customer) ---------- */
  .navbar {
    background: white;
    padding: 14px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #F2E8EC;
    position: sticky;
    top: 0;
    z-index: 50;
  }
  .navbar .brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
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

  .nav-links {
    display: flex;
    gap: 28px;
    margin-left: 40px;
    flex: 1;
  }
  .nav-links a {
    font-size: 13.5px;
    color: #666;
    text-decoration: none;
    font-weight: 500;
    transition: 0.2s;
    padding: 6px 0;
  }
  .nav-links a:hover { color: #6E2C4A; }
  .nav-links a.active {
    color: #6E2C4A;
    font-weight: 700;
    border-bottom: 2px solid #6E2C4A;
  }

  .nav-actions {
    display: flex;
    align-items: center;
    gap: 16px;
  }
  .nav-actions .icon-btn {
    background: transparent;
    border: none;
    font-size: 18px;
    cursor: pointer;
    opacity: 0.6;
    transition: 0.2s;
    position: relative;
  }
  .nav-actions .icon-btn:hover { opacity: 1; }
  .nav-actions .cart-badge {
    position: absolute;
    top: -4px;
    right: -6px;
    background: #6E2C4A;
    color: white;
    font-size: 9.5px;
    font-weight: 700;
    padding: 2px 5px;
    border-radius: 10px;
    min-width: 16px;
    text-align: center;
  }
  .nav-actions .user-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #F7F0F3;
    border: none;
    padding: 6px 14px 6px 6px;
    border-radius: 30px;
    cursor: pointer;
    transition: 0.2s;
  }
  .nav-actions .user-btn:hover { background: #EDE3E7; }
  .nav-actions .user-btn .avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #6E2C4A;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
  }
  .nav-actions .user-btn span {
    font-size: 12.5px;
    color: #4A1F33;
    font-weight: 600;
  }
  .nav-actions .user-menu { position: relative; }
  .nav-actions .user-menu summary { list-style: none; cursor: pointer; }
  .nav-actions .user-menu summary::-webkit-details-marker { display: none; }
  .user-menu-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    min-width: 150px;
    padding: 6px;
    background: white;
    border: 1px solid #F2E8EC;
    border-radius: 10px;
    box-shadow: 0 5px 18px rgba(74, 31, 51, 0.14);
    z-index: 60;
  }
  .user-menu-dropdown a {
    display: block;
    padding: 9px 12px;
    border-radius: 6px;
    color: #6E2C4A;
    font-size: 13px;
    text-decoration: none;
  }
  .user-menu-dropdown a:hover,
  .user-menu-dropdown a:focus { background: #F7F0F3; }

  /* ---------- Hero Banner ---------- */
  .hero {
    background: linear-gradient(135deg, #F7F0F3 0%, #EDE3E7 100%);
    padding: 50px 40px 45px;
    text-align: center;
    border-bottom: 1px solid #F2E8EC;
  }
  .hero h1 {
    font-size: 32px;
    font-weight: 800;
    color: #4A1F33;
    margin-bottom: 10px;
    letter-spacing: -0.5px;
  }
  .hero p {
    font-size: 14px;
    color: #8B6A7A;
    max-width: 520px;
    margin: 0 auto;
    line-height: 1.6;
  }

  /* ---------- Main Content ---------- */
  .main {
    max-width: 1200px;
    margin: 0 auto;
    padding: 32px 40px 60px;
  }

  /* ---------- Toolbar ---------- */
  .toolbar {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 24px;
  }
  .search-bar {
    position: relative;
    flex: 1;
    min-width: 240px;
    max-width: 420px;
  }
  .search-bar input {
    width: 100%;
    padding: 11px 16px 11px 40px;
    border: 1px solid #EDE3E7;
    border-radius: 8px;
    font-size: 13px;
    background: white;
    color: #333;
    transition: 0.2s;
  }
  .search-bar input::placeholder { color: #B5B5B5; }
  .search-bar input:focus {
    outline: none;
    border-color: #6E2C4A;
    box-shadow: 0 0 0 3px rgba(110,44,74,0.1);
  }
  .search-bar::before {
    content: "🔍";
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 13px;
    opacity: 0.4;
  }
  .filter-select {
    padding: 11px 16px;
    border: 1px solid #EDE3E7;
    border-radius: 8px;
    font-size: 13px;
    background: white;
    color: #555;
    cursor: pointer;
    transition: 0.2s;
  }
  .filter-select:focus {
    outline: none;
    border-color: #6E2C4A;
    box-shadow: 0 0 0 3px rgba(110,44,74,0.1);
  }
  .sort-select {
    padding: 11px 16px;
    border: 1px solid #EDE3E7;
    border-radius: 8px;
    font-size: 13px;
    background: white;
    color: #555;
    cursor: pointer;
    margin-left: auto;
  }

  /* ---------- Result Count ---------- */
  .result-count {
    font-size: 13px;
    color: #8B6A7A;
    margin-bottom: 18px;
  }
  .result-count strong { color: #4A1F33; font-weight: 700; }

  /* ---------- Catalog Grid ---------- */
  .catalog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 22px;
  }

  /* ---------- Cake Card (Customer) ---------- */
  .cake-card {
    background: white;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #F2E8EC;
    box-shadow: 0 2px 8px rgba(74,31,51,0.04);
    transition: 0.25s;
    display: flex;
    flex-direction: column;
  }
  .cake-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(110,44,74,0.13);
    border-color: #EDE3E7;
  }

  /* Cake Image */
  .cake-image {
    height: 180px;
    flex: 0 0 180px;
    background: linear-gradient(135deg, #F7F0F3, #EDE3E7);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
  }
  .cake-image img {
    display: block;
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
    padding: 18px;
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
    margin-bottom: 6px;
  }
  .cake-name {
    font-size: 15px;
    font-weight: 700;
    color: #4A1F33;
    margin-bottom: 8px;
    line-height: 1.3;
  }
  .cake-desc {
    font-size: 12.5px;
    color: #8B6A7A;
    line-height: 1.55;
    margin-bottom: 14px;
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
    padding-top: 14px;
    border-top: 1px solid #F7F0F3;
  }
  .cake-price {
    font-size: 18px;
    font-weight: 800;
    color: #6E2C4A;
  }
  .cake-price small {
    font-size: 10.5px;
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

  /* Cake Actions (customer) */
  .cake-actions {
    padding: 0 18px 18px;
  }
  .btn-add-cart {
    width: 100%;
    padding: 10px;
    background: #6E2C4A;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
  }
  .btn-add-cart:hover { background: #5A2139; }
  .btn-add-cart:disabled {
    background: #EDE3E7;
    color: #B5A0AA;
    cursor: not-allowed;
  }

  .empty-catalog {
    text-align: center;
    padding: 36px 16px;
    color: #8B6A7A;
    font-size: 13.5px;
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
    border-radius: 14px;
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
    border-radius: 14px 14px 0 0;
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
    height: 200px;
    background: linear-gradient(135deg, #F7F0F3, #EDE3E7);
    border-radius: 10px;
    margin-bottom: 20px;
    overflow: hidden;
  }
  .modal-cake-image img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .modal-cake-name {
    font-size: 19px;
    font-weight: 700;
    color: #4A1F33;
    margin-bottom: 4px;
  }
  .modal-cake-cat {
    font-size: 11px;
    color: #6E2C4A;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    margin-bottom: 14px;
  }
  .modal-cake-desc {
    font-size: 13px;
    color: #666;
    line-height: 1.6;
    margin-bottom: 18px;
    padding-bottom: 18px;
    border-bottom: 1px solid #F7F0F3;
  }

  .detail-row {
    display: flex;
    padding: 10px 0;
    border-bottom: 1px solid #F7F0F3;
    font-size: 13px;
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
  .detail-value.price {
    color: #6E2C4A;
    font-weight: 800;
    font-size: 18px;
  }

  .modal-footer {
    padding: 16px 22px 22px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid #F7F0F3;
  }
  .btn-secondary {
    background: white;
    color: #6E2C4A;
    border: 1px solid #EDE3E7;
    padding: 10px 20px;
    border-radius: 8px;
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
    padding: 10px 22px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    transition: 0.2s;
  }
  .btn-primary:hover { background: #5A2139; }

  /* ---------- Footer ---------- */
  .footer {
    background: white;
    border-top: 1px solid #F2E8EC;
    padding: 24px 40px;
    text-align: center;
    font-size: 12px;
    color: #999;
  }
  .footer strong { color: #6E2C4A; }

  @media (max-width: 900px) {
    .navbar { padding: 12px 20px; }
    .nav-links { display: none; }
    .hero { padding: 40px 20px 35px; }
    .hero h1 { font-size: 24px; }
    .main { padding: 24px 20px 40px; }
    .footer { padding: 20px; }
  }
</style>
</head>
<body>

<!-- Top Navigation -->
<div class="navbar">
  <a href="portal_catalog.php" class="brand">
    <div class="logo-box">🎂</div>
    <div class="brand-text">
      <div class="name">SweetBites</div>
    </div>
  </a>

  <nav class="nav-links">
    <a href="portal_catalog.php">Home</a>
    <a href="portal_catalog.php" class="active">Cake Catalog</a>
    <a href="portal_catalog.php">About Us</a>
  </nav>

  <div class="nav-actions">
    <button type="button" class="icon-btn" title="Cart">
      🛒
    </button>
    <details class="user-menu">
      <summary class="user-btn">
        <div class="avatar">F</div>
        <span>Fatimah</span>
      </summary>
      <div class="user-menu-dropdown">
        <a href="login.php">Logout</a>
      </div>
    </details>
  </div>
</div>

<!-- Hero Banner -->
<div class="hero">
  <h1>Our Delicious Cakes</h1>
  <p>Handcrafted with love, baked fresh daily. Browse our selection of cakes for every occasion.</p>
</div>

<!-- Main Content -->
<main class="main">

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search cakes by name, flavor, or category..." oninput="applyCatalogFilters()">
    </div>
    <select class="filter-select" id="categoryFilter" onchange="applyCatalogFilters()">
      <option value="all">All Categories</option>
      <option value="Birthday Cake">Birthday Cake</option>
      <option value="Wedding Cake">Wedding Cake</option>
      <option value="Cupcake">Cupcake</option>
      <option value="Cheesecake">Cheesecake</option>
      <option value="Custom Cake">Custom Cake</option>
    </select>
    <select class="sort-select" id="sortSelect" onchange="applyCatalogFilters()">
      <option value="featured">Sort: Featured</option>
      <option value="price-asc">Price: Low to High</option>
      <option value="price-desc">Price: High to Low</option>
      <option value="newest">Newest</option>
    </select>
  </div>

  <div class="result-count">
    Showing <strong id="resultCount"><?php echo count($products); ?></strong> cakes
  </div>

  <!-- Catalog Grid -->
  <div class="catalog-grid" id="catalogGrid">
    <?php if (!empty($products)): ?>
      <?php foreach ($products as $index => $cake): ?>
        <?php
          $id = (int) ($cake["product_id"] ?? 0);
          $name = $cake["product_name"] ?? "";
          $category = $cake["category"] ?? "";
          $description = $cake["description"] ?? "";
          $flavor = $cake["flavor"] ?? "-";
          $size = $cake["size"] ?? "-";
          $priceNum = (float) ($cake["price"] ?? 0);
          $priceFormatted = number_format($priceNum, 2, ".", "");
          $availability = $cake["availability"] ?? "Available";
          $imagePath = !empty($cake["image_path"]) ? $cake["image_path"] : "Images/choco_fudge.jpg";
          $badgeClass = getCustomerBadgeClass($availability);
        ?>
        <div class="cake-card"
             data-featured="<?php echo $index; ?>"
             data-id="<?php echo $id; ?>"
             data-name="<?php echo htmlspecialchars(strtolower($name), ENT_QUOTES, 'UTF-8'); ?>"
             data-flavor="<?php echo htmlspecialchars(strtolower($flavor), ENT_QUOTES, 'UTF-8'); ?>"
             data-category="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>"
             data-price="<?php echo htmlspecialchars((string) $priceNum, ENT_QUOTES, 'UTF-8'); ?>"
             data-cake-name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
             data-cake-category="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>"
             data-cake-desc="<?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>"
             data-cake-flavor="<?php echo htmlspecialchars($flavor, ENT_QUOTES, 'UTF-8'); ?>"
             data-cake-size="<?php echo htmlspecialchars($size, ENT_QUOTES, 'UTF-8'); ?>"
             data-cake-status="<?php echo htmlspecialchars($availability, ENT_QUOTES, 'UTF-8'); ?>"
             data-cake-badge="<?php echo htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8'); ?>"
             data-cake-price="<?php echo htmlspecialchars($priceFormatted, ENT_QUOTES, 'UTF-8'); ?>"
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
              <div class="cake-price">RM <?php echo htmlspecialchars($priceFormatted, ENT_QUOTES, 'UTF-8'); ?> <small>per cake</small></div>
              <div class="cake-size"><?php echo htmlspecialchars($size, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
          </div>
          <div class="cake-actions">
            <button type="button" class="btn-add-cart" onclick="openModal(this)">👁 View Details</button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="empty-catalog" id="emptyCatalog" style="display: <?php echo empty($products) ? 'block' : 'none'; ?>;">
    No cakes match your search or filter.
  </div>
</main>

<!-- ---------- Detail View Modal (Customer) ---------- -->
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

      <div class="modal-cake-cat" id="modalCategory">-</div>
      <div class="modal-cake-name" id="modalName">-</div>
      <div class="modal-cake-desc" id="modalDesc">-</div>

      <div class="detail-row">
        <div class="detail-label">Flavor</div>
        <div class="detail-value" id="modalFlavor">-</div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Size</div>
        <div class="detail-value" id="modalSize">-</div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Availability</div>
        <div class="detail-value">
          <span class="status-badge badge-available" id="modalBadge" style="position:static; display:inline-block;">AVAILABLE</span>
        </div>
      </div>
      <div class="detail-row">
        <div class="detail-label">Price</div>
        <div class="detail-value price" id="modalPrice">RM 0.00</div>
      </div>
    </div>

    <div class="modal-footer">
      <button type="button" class="btn-secondary" onclick="closeModal()">Close</button>
      <button type="button" class="btn-primary">🛒 Add to Cart</button>
    </div>

  </div>
</div>

<!-- Footer -->
<div class="footer">
  © 2026 <strong>SweetBites</strong> · All rights reserved
</div>

<script>
  function openModal(button) {
    const card = button.closest('.cake-card');
    if (!card) return;

    const name = card.dataset.cakeName || '-';
    const category = card.dataset.cakeCategory || '-';
    const desc = card.dataset.cakeDesc || '-';
    const flavor = card.dataset.cakeFlavor || '-';
    const size = card.dataset.cakeSize || '-';
    const status = card.dataset.cakeStatus || 'Available';
    const badgeClass = card.dataset.cakeBadge || 'badge-available';
    const price = card.dataset.cakePrice || '0.00';
    const image = card.dataset.cakeImage || 'Images/choco_fudge.jpg';

    document.getElementById('modalImage').src = image;
    document.getElementById('modalImage').alt = name;
    document.getElementById('modalCategory').textContent = category;
    document.getElementById('modalName').textContent = name;
    document.getElementById('modalDesc').textContent = desc;
    document.getElementById('modalFlavor').textContent = flavor;
    document.getElementById('modalSize').textContent = size;
    document.getElementById('modalPrice').textContent = `RM ${price}`;

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

  function applyCatalogFilters() {
    const query = document.getElementById('searchInput').value.toLowerCase().trim();
    const category = document.getElementById('categoryFilter').value;
    const sort = document.getElementById('sortSelect').value;

    const grid = document.getElementById('catalogGrid');
    const cards = Array.from(grid.querySelectorAll('.cake-card'));

    cards.sort((a, b) => {
      if (sort === 'price-asc') {
        return parseFloat(a.dataset.price || 0) - parseFloat(b.dataset.price || 0);
      }
      if (sort === 'price-desc') {
        return parseFloat(b.dataset.price || 0) - parseFloat(a.dataset.price || 0);
      }
      if (sort === 'newest') {
        return parseInt(b.dataset.id || 0, 10) - parseInt(a.dataset.id || 0, 10);
      }
      return parseInt(a.dataset.featured || 0, 10) - parseInt(b.dataset.featured || 0, 10);
    });

    cards.forEach(card => grid.appendChild(card));

    let visibleCount = 0;
    cards.forEach(card => {
      const name = (card.dataset.name || '').toLowerCase();
      const flavor = (card.dataset.flavor || '').toLowerCase();
      const cardCat = card.dataset.category || '';

      const matchesSearch = !query || name.includes(query) || flavor.includes(query) || cardCat.toLowerCase().includes(query);
      const matchesCat = category === 'all' || cardCat === category;

      const show = matchesSearch && matchesCat;
      card.style.display = show ? 'flex' : 'none';
      if (show) visibleCount++;
    });

    document.getElementById('resultCount').textContent = visibleCount;
    document.getElementById('emptyCatalog').style.display = visibleCount === 0 ? 'block' : 'none';
  }
</script>

</body>
</html>