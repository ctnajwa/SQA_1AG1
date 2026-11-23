<?php
include "db.php";

$totalProducts = 0;
$lowStockCount = 0;
$activeSuppliers = 0;
$pendingOrders = 0;
$lowStockItems = [];
$expiringItems = [];

if ($res = $conn->query("SELECT COUNT(*) AS cnt FROM products")) {
    if ($row = $res->fetch_assoc()) {
        $totalProducts = (int) $row['cnt'];
    }
    $res->free();
}

if ($res = $conn->query("SELECT ingredient_name, category, quantity, unit FROM ingredients WHERE (expiry_date IS NULL OR expiry_date >= CURDATE()) AND quantity <= 10 ORDER BY quantity ASC, ingredient_name ASC")) {
    while ($row = $res->fetch_assoc()) {
        $lowStockItems[] = $row;
    }
    $lowStockCount = count($lowStockItems);
    $res->free();
}

if ($res = $conn->query("SELECT COUNT(*) AS cnt FROM supplier WHERE status = 'Active'")) {
    if ($row = $res->fetch_assoc()) {
        $activeSuppliers = (int) $row['cnt'];
    }
    $res->free();
}

if ($res = $conn->query("SELECT COUNT(*) AS cnt FROM purchase_orders WHERE status = 'Pending'")) {
    if ($row = $res->fetch_assoc()) {
        $pendingOrders = (int) $row['cnt'];
    }
    $res->free();
}

if ($res = $conn->query("SELECT ingredient_name, category, expiry_date FROM ingredients WHERE expiry_date IS NOT NULL AND expiry_date >= CURDATE() AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY expiry_date ASC")) {
    while ($row = $res->fetch_assoc()) {
        $expiringItems[] = $row;
    }
    $res->free();
}

$days = [];
$purchasesByDate = [];
for ($i = 6; $i >= 0; $i--) {
    $dateStr = date('Y-m-d', strtotime("-{$i} days"));
    $days[] = [
        'date'  => $dateStr,
        'label' => date('D', strtotime($dateStr)),
    ];
    $purchasesByDate[$dateStr] = 0.0;
}

$sqlPurchases = "
    SELECT po.order_date, COALESCE(SUM(poi.quantity * poi.unit_price), 0) AS total_amount
    FROM purchase_orders po
    LEFT JOIN purchase_order_items poi ON po.purchase_order_id = poi.purchase_order_id
    WHERE po.status <> 'Cancelled'
      AND po.order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
      AND po.order_date <= CURDATE()
    GROUP BY po.order_date
";
if ($res = $conn->query($sqlPurchases)) {
    while ($row = $res->fetch_assoc()) {
        $d = $row['order_date'];
        if (isset($purchasesByDate[$d])) {
            $purchasesByDate[$d] = (float) $row['total_amount'];
        }
    }
    $res->free();
}

$purchaseSeries = [];
$labels = [];
foreach ($days as $d) {
    $labels[] = $d['label'];
    $purchaseSeries[] = round($purchasesByDate[$d['date']], 2);
}

// Placeholder until a sales table is added to cake_shop_inventory.sql
$salesSeries = [180, 350, 620, 490, 820, 680, 930];

$conn->close();

$xCoords = [90, 190, 290, 390, 490, 590, 670];
$yZero = 200.0;
$yTop  = 20.0;
$plotHeight = $yZero - $yTop;

$maxVal = max(array_merge($purchaseSeries, $salesSeries, [1000]));
$maxScale = (int) (ceil($maxVal / 250.0) * 250);
if ($maxScale < 1000) {
    $maxScale = 1000;
}

$yTicks = [
    100 => (int) $maxScale,
    75 => (int) round($maxScale * 0.75),
    50 => (int) round($maxScale * 0.50),
    25 => (int) round($maxScale * 0.25),
    0 => 0,
];

$purchasePoints = [];
$salesPoints = [];
for ($i = 0; $i < 7; $i++) {
    $px = $xCoords[$i];
    $py = round($yZero - (($purchaseSeries[$i] / $maxScale) * $plotHeight), 1);
    $purchasePoints[] = [
        'x'     => $px,
        'y'     => $py,
        'value' => $purchaseSeries[$i],
        'label' => $labels[$i],
    ];

    $sy = round($yZero - (($salesSeries[$i] / $maxScale) * $plotHeight), 1);
    $salesPoints[] = [
        'x'     => $px,
        'y'     => $sy,
        'value' => $salesSeries[$i],
        'label' => $labels[$i],
    ];
}

$purchasePolyline = implode(' ', array_map(fn($p) => "{$p['x']},{$p['y']}", $purchasePoints));
$salesPolyline    = implode(' ', array_map(fn($p) => "{$p['x']},{$p['y']}", $salesPoints));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - SweetBites</title>
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
  .user-menu details { position: relative; }
  .user-menu summary {
    list-style: none;
    cursor: pointer;
  }
  .user-menu summary::-webkit-details-marker { display: none; }
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
  .dashboard { display: flex; min-height: calc(100vh - 63px); }

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
  .main-content {
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
  .page-header h2 {
    font-size: 22px;
    font-weight: 700;
    color: #4A1F33;
    margin-bottom: 4px;
  }
  .page-header p { font-size: 12.5px; color: #999; }

  /* Dashboard specific metric cards layout */
  .metrics-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 24px;
  }
  .metric-card {
    background-color: white;
    border: 1px solid #EEE;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    display: flex;
    align-items: center;
    gap: 15px;
  }
  .metric-icon {
    width: 44px;
    height: 44px;
    background-color: #F7F0F3;
    color: #6E2C4A;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    border: 1px solid #EDE3E7;
  }
  .metric-info h3 {
    margin: 0 0 2px;
    font-size: 20px;
    font-weight: 700;
    color: #4A1F33;
  }
  .metric-info p {
    margin: 0;
    color: #999;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }

  /* ==========================================================
     Alert cards (Low Stock + Expiring Soon)
     ========================================================== */
  .alerts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 24px;
  }
  .alert-panel {
    background-color: white;
    border: 1px solid #EEE;
    border-radius: 10px;
    padding: 20px 22px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
  }
  .alert-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
  }
  .alert-panel-header h4 {
    font-size: 14px;
    font-weight: 700;
    color: #4A1F33;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .alert-panel-header .view-all {
    font-size: 11.5px;
    color: #6E2C4A;
    font-weight: 600;
    text-decoration: none;
  }
  .alert-panel-header .view-all:hover { text-decoration: underline; }

  .alert-list { display: flex; flex-direction: column; }
  .alert-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 11px 0;
    border-bottom: 1px solid #F7F0F3;
    font-size: 12.5px;
  }
  .alert-row:last-child { border-bottom: none; padding-bottom: 0; }
  .alert-row:first-child { padding-top: 0; }

  .alert-item-name {
    font-weight: 600;
    color: #4A1F33;
    margin-bottom: 3px;
  }
  .alert-item-meta {
    font-size: 11px;
    color: #999;
  }
  .alert-item-meta .danger { color: #E53935; font-weight: 700; }
  .alert-item-meta .warning { color: #C77700; font-weight: 700; }

  .alert-empty {
    font-size: 12px;
    color: #999;
    padding: 8px 0;
  }

  .alert-action {
    padding: 5px 12px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid #6E2C4A;
    background: white;
    color: #6E2C4A;
    text-decoration: none;
    transition: 0.2s;
    flex-shrink: 0;
  }
  .alert-action:hover { background: #6E2C4A; color: white; }

  /* ==========================================================
     Line chart (Purchases vs Sales)
     ========================================================== */
  .chart-panel {
    background-color: white;
    border: 1px solid #EEE;
    border-radius: 10px;
    padding: 20px 22px 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    margin-bottom: 24px;
  }
  .chart-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
    flex-wrap: wrap;
    gap: 12px;
  }
  .chart-panel-header h4 {
    font-size: 14px;
    font-weight: 700;
    color: #4A1F33;
    margin-bottom: 3px;
  }
  .chart-panel-header p {
    font-size: 11.5px;
    color: #999;
  }
  .chart-legend {
    display: flex;
    gap: 18px;
    font-size: 11.5px;
    color: #666;
    align-items: center;
  }
  .legend-item { display: flex; align-items: center; gap: 6px; }
  .legend-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
  }
  .legend-dot.purchases { background: #6E2C4A; }
  .legend-dot.sales { background: #E8A0B8; }

  .chart-svg-wrap {
    width: 100%;
    height: 220px;
  }
  .chart-svg-wrap svg {
    width: 100%;
    height: 100%;
    overflow: visible;
  }

  /* Responsive tweaks */
  @media (max-width: 1100px) {
    .metrics-grid { grid-template-columns: repeat(2, 1fr); }
  }
  @media (max-width: 900px) {
    .alerts-grid { grid-template-columns: 1fr; }
  }
  @media (max-width: 600px) {
    .metrics-grid { grid-template-columns: 1fr; }
    .sidebar { display: none; }
    .main-content { padding: 20px; }
    .chart-legend { flex-direction: column; align-items: flex-start; gap: 6px; }
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

<div class="dashboard">

  <!-- Sidebar -->
  <aside class="sidebar">
    <nav>
      <a href="admin_dashboard.php" class="nav-item active">
         Dashboard
      </a>
      <a href="admin_ingredient.php" class="nav-item">
         Ingredient Stock
      </a>
      <a href="supplier.php" class="nav-item">
         Supplier
      </a>
      <a href="admin_purchase_orders.php" class="nav-item">
         Purchasing Order
      </a>
      <a href="admin_catalog.php" class="nav-item">
         Product Catalog
      </a>
    </nav>
  </aside>

  <!-- Main Content Area -->
  <div class="main-content">

    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h2>Dashboard</h2>
        <p>Welcome back, Administrator</p>
      </div>
    </div>

    <!-- Page Content -->
    <div class="content">

      <!-- Metrics Overview -->
      <div class="metrics-grid">
        <div class="metric-card">
          <div class="metric-icon">🍰</div>
          <div class="metric-info">
            <h3><?php echo $totalProducts; ?></h3>
            <p>Total Products</p>
          </div>
        </div>
        <div class="metric-card">
          <div class="metric-icon">⚠️</div>
          <div class="metric-info">
            <h3><?php echo $lowStockCount; ?></h3>
            <p>Low Stock Items</p>
          </div>
        </div>
        <div class="metric-card">
          <div class="metric-icon">🚚</div>
          <div class="metric-info">
            <h3><?php echo $activeSuppliers; ?></h3>
            <p>Active Suppliers</p>
          </div>
        </div>
        <div class="metric-card">
          <div class="metric-icon">📋</div>
          <div class="metric-info">
            <h3><?php echo $pendingOrders; ?></h3>
            <p>Pending Orders</p>
          </div>
        </div>
      </div>

      <!-- ==========================================================
           Alerts Row (Low Stock + Expiring Soon)
           ========================================================== -->
      <div class="alerts-grid">

        <!-- Low Stock Alert Panel -->
        <div class="alert-panel">
          <div class="alert-panel-header">
            <h4>⚠️ Low Stock Alerts</h4>
            <a href="admin_ingredient.php" class="view-all">View all →</a>
          </div>
          <div class="alert-list">
            <?php if (!empty($lowStockItems)): ?>
              <?php foreach ($lowStockItems as $item): ?>
                <div class="alert-row">
                  <div>
                    <div class="alert-item-name"><?php echo htmlspecialchars($item['ingredient_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="alert-item-meta">
                      <?php echo htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?> ·
                      <span class="danger"><?php echo htmlspecialchars(rtrim(rtrim((string)$item['quantity'], '0'), '.'), ENT_QUOTES, 'UTF-8') . ' ' . strtolower(htmlspecialchars($item['unit'], ENT_QUOTES, 'UTF-8')); ?> left</span> ·
                      threshold 10 <?php echo strtolower(htmlspecialchars($item['unit'], ENT_QUOTES, 'UTF-8')); ?>
                    </div>
                  </div>
                  <a href="admin_purchase_orders.php" class="alert-action">Reorder</a>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="alert-empty">No low stock ingredients right now.</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Expiring Soon Alert Panel -->
        <div class="alert-panel">
          <div class="alert-panel-header">
            <h4>⏰ Expiring Soon</h4>
            <a href="admin_ingredient.php" class="view-all">View all →</a>
          </div>
          <div class="alert-list">
            <?php if (!empty($expiringItems)): ?>
              <?php foreach ($expiringItems as $item): ?>
                <?php
                  $daysLeft = (int) ((strtotime($item['expiry_date']) - strtotime(date('Y-m-d'))) / 86400);
                  $formattedDate = date('d M Y', strtotime($item['expiry_date']));
                ?>
                <div class="alert-row">
                  <div>
                    <div class="alert-item-name"><?php echo htmlspecialchars($item['ingredient_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="alert-item-meta">
                      <?php echo htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8'); ?> ·
                      <span class="warning">Expires in <?php echo $daysLeft; ?> day<?php echo $daysLeft === 1 ? '' : 's'; ?></span> ·
                      <?php echo htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                  </div>
                  <a href="admin_ingredient.php" class="alert-action">Use Soon</a>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="alert-empty">No ingredients expiring within 7 days.</div>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- ==========================================================
           Line Chart — Purchases vs Sales
           ========================================================== -->
      <div class="chart-panel">
        <div class="chart-panel-header">
          <div>
            <h4>Purchases vs Sales</h4>
            <p>Last 7 days · Amount in RM</p>
          </div>
          <div class="chart-legend">
            <div class="legend-item"><span class="legend-dot purchases"></span> Purchases</div>
            <div class="legend-item"><span class="legend-dot sales"></span> Sales</div>
          </div>
        </div>

        <div class="chart-svg-wrap">
          <svg viewBox="0 0 700 220" preserveAspectRatio="none">
            <!-- Y-axis gridlines -->
            <line x1="40" y1="20"  x2="680" y2="20"  stroke="#F2F2F2" stroke-width="1"/>
            <line x1="40" y1="65"  x2="680" y2="65"  stroke="#F2F2F2" stroke-width="1"/>
            <line x1="40" y1="110" x2="680" y2="110" stroke="#F2F2F2" stroke-width="1"/>
            <line x1="40" y1="155" x2="680" y2="155" stroke="#F2F2F2" stroke-width="1"/>
            <line x1="40" y1="200" x2="680" y2="200" stroke="#DDD"    stroke-width="1"/>

            <!-- Y-axis labels -->
            <?php foreach ($yTicks as $percentage => $tickVal): ?>
              <?php $ty = round($yZero - (($percentage / 100) * $plotHeight) + 4, 1); ?>
              <text x="30" y="<?php echo $ty; ?>" text-anchor="end" font-size="10" fill="#B5B5B5"><?php echo $tickVal; ?></text>
            <?php endforeach; ?>

            <!-- Purchases line (dark plum, from database) -->
            <polyline
              fill="none"
              stroke="#6E2C4A"
              stroke-width="3"
              stroke-linecap="round"
              stroke-linejoin="round"
              points="<?php echo htmlspecialchars($purchasePolyline, ENT_QUOTES, 'UTF-8'); ?>"
            />
            <?php foreach ($purchasePoints as $pt): ?>
              <circle cx="<?php echo $pt['x']; ?>" cy="<?php echo $pt['y']; ?>" r="4" fill="#6E2C4A">
                <title>Purchases (<?php echo htmlspecialchars($pt['label'], ENT_QUOTES, 'UTF-8'); ?>): RM <?php echo number_format($pt['value'], 2); ?></title>
              </circle>
            <?php endforeach; ?>

            <!-- Sales line (soft pink) -->
            <polyline
              fill="none"
              stroke="#E8A0B8"
              stroke-width="3"
              stroke-linecap="round"
              stroke-linejoin="round"
              points="<?php echo htmlspecialchars($salesPolyline, ENT_QUOTES, 'UTF-8'); ?>"
            />
            <?php foreach ($salesPoints as $pt): ?>
              <circle cx="<?php echo $pt['x']; ?>" cy="<?php echo $pt['y']; ?>" r="4" fill="#E8A0B8">
                <title>Sales (<?php echo htmlspecialchars($pt['label'], ENT_QUOTES, 'UTF-8'); ?>): RM <?php echo number_format($pt['value'], 2); ?></title>
              </circle>
            <?php endforeach; ?>

            <!-- X-axis labels -->
            <?php foreach ($purchasePoints as $pt): ?>
              <text x="<?php echo $pt['x']; ?>" y="218" text-anchor="middle" font-size="10" fill="#999"><?php echo htmlspecialchars($pt['label'], ENT_QUOTES, 'UTF-8'); ?></text>
            <?php endforeach; ?>
          </svg>
        </div>
      </div>

    </div>

  </div>

</div>

</body>
</html>