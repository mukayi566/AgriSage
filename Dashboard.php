<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Include database configuration
include_once 'config/database.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];
$user_email = $_SESSION['email'];

// Get user stats
$stats = [];
$queries = [
    'total_crops' => "SELECT COUNT(*) as count FROM crops WHERE user_id = ?",
    'recent_scans' => "SELECT COUNT(*) as count FROM crops WHERE user_id = ? AND scan_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
    'disease_crops' => "SELECT COUNT(*) as count FROM crops WHERE user_id = ? AND disease_status != 'healthy' AND disease_status IS NOT NULL",
    'healthy_crops' => "SELECT COUNT(*) as count FROM crops WHERE user_id = ? AND (disease_status = 'healthy' OR disease_status IS NULL)",
    'pending_drone' => "SELECT COUNT(*) as count FROM drone_services WHERE user_id = ? AND status = 'pending'",
    'total_orders' => "SELECT COUNT(*) as count FROM orders WHERE user_id = ?"
];

foreach ($queries as $key => $query) {
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id]);
    $stats[$key] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

// Get crop health distribution
$health_query = "
    SELECT 
        CASE 
            WHEN disease_status IS NULL OR disease_status = 'healthy' THEN 'Healthy'
            ELSE 'Diseased'
        END as health_status,
        COUNT(*) as count
    FROM crops 
    WHERE user_id = ? 
    GROUP BY health_status
";
$health_stmt = $db->prepare($health_query);
$health_stmt->execute([$user_id]);
$health_stats = $health_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent crops with details
$recent_crops_query = "
    SELECT *, 
           CASE 
               WHEN disease_status IS NULL OR disease_status = 'healthy' THEN 'healthy'
               ELSE 'diseased'
           END as overall_health
    FROM crops 
    WHERE user_id = ? 
    ORDER BY scan_date DESC 
    LIMIT 6
";
$recent_crops_stmt = $db->prepare($recent_crops_query);
$recent_crops_stmt->execute([$user_id]);
$recent_crops = $recent_crops_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent activities
$activities_query = "
    SELECT * FROM activities 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 8
";
$activities_stmt = $db->prepare($activities_query);
$activities_stmt->execute([$user_id]);
$activities = $activities_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get notifications
$notifications_query = "
    SELECT * FROM notifications 
    WHERE user_id IS NULL OR user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 8
";
$notifications_stmt = $db->prepare($notifications_query);
$notifications_stmt->execute([$user_id]);
$notifications = $notifications_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get products
$products_query = "
    SELECT * FROM products 
    WHERE stock > 0 
    ORDER BY created_at DESC 
    LIMIT 4
";
$products_stmt = $db->prepare($products_query);
$products_stmt->execute();
$products = $products_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get drone services
$drone_services_query = "
    SELECT * FROM drone_services 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 3
";
$drone_services_stmt = $db->prepare($drone_services_query);
$drone_services_stmt->execute([$user_id]);
$drone_services = $drone_services_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get weather data (mock data - you can integrate with weather API)
$weather_data = [
    'temperature' => 28,
    'humidity' => 65,
    'rain_chance' => 20,
    'wind_speed' => 12,
    'condition' => 'partly_cloudy'
];

// Get market prices (mock data)
$market_prices = [
    ['crop' => 'Maize', 'price' => 185, 'change' => 2.5],
    ['crop' => 'Wheat', 'price' => 210, 'change' => -1.2],
    ['crop' => 'Rice', 'price' => 320, 'change' => 3.1],
    ['crop' => 'Soybean', 'price' => 280, 'change' => 0.8]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriSage - Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2c5530;
            --secondary: #4a7c59;
            --accent: #7bc142;
            --light: #f8f9fa;
            --dark: #1a3a1e;
            --danger: #ff4757;
            --warning: #ffa502;
            --success: #2ed573;
            --info: #1e90ff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        /* Header */
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 0.5rem;
            color: var(--accent);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .user-email {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Main Layout */
        .dashboard-container {
            max-width: 1400px;
            margin: 80px auto 0;
            padding: 2rem;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            min-height: calc(100vh - 80px);
        }

        /* Sidebar */
        .sidebar {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .user-profile {
            text-align: center;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--light);
            margin-bottom: 1.5rem;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }

        .profile-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.2rem;
        }

        .profile-role {
            color: var(--secondary);
            font-size: 0.9rem;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            color: #555;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-item a i {
            margin-right: 0.8rem;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .nav-item a:hover, .nav-item a.active {
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            color: white;
            transform: translateX(5px);
        }

        /* Main Content */
        .main-content {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: rgba(255,255,255,0.1);
            transform: rotate(45deg);
        }

        .welcome-section h1 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            position: relative;
        }

        .welcome-section p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid var(--accent);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .stat-info {
            flex: 1;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 0.2rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .stat-trend {
            font-size: 0.8rem;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
            background: var(--success);
            color: white;
        }

        /* Charts and Analytics */
        .analytics-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .chart-container, .info-container {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .chart-header, .info-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light);
        }

        .chart-header h2, .info-header h2 {
            color: var(--primary);
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .view-all {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Crop Health Chart */
        .health-chart {
            height: 300px;
            position: relative;
        }

        /* Quick Stats */
        .quick-stats {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .quick-stat-item {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 1rem;
            background: var(--light);
            border-radius: 10px;
            border-left: 4px solid var(--accent);
        }

        .quick-stat-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 1rem;
        }

        .icon-healthy { background: var(--success); }
        .icon-diseased { background: var(--danger); }
        .icon-pending { background: var(--warning); }
        .icon-total { background: var(--info); }

        .quick-stat-info {
            flex: 1;
        }

        .quick-stat-value {
            font-weight: bold;
            color: var(--primary);
            font-size: 1.1rem;
        }

        .quick-stat-label {
            color: #666;
            font-size: 0.8rem;
        }

        /* Recent Activity */
        .activity-list {
            list-style: none;
        }

        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid var(--light);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: background 0.3s ease;
        }

        .activity-item:hover {
            background: var(--light);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .activity-info {
            flex: 1;
        }

        .activity-info h4 {
            margin-bottom: 0.2rem;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .activity-info p {
            color: #666;
            font-size: 0.8rem;
        }

        .activity-time {
            color: #999;
            font-size: 0.7rem;
        }

        /* Notifications */
        .notification-list {
            list-style: none;
        }

        .notification-item {
            padding: 1rem;
            border-bottom: 1px solid var(--light);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            transition: background 0.3s ease;
        }

        .notification-item:hover {
            background: var(--light);
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
            margin-top: 0.2rem;
        }

        .icon-alert { background: var(--danger); }
        .icon-weather { background: var(--info); }
        .icon-market { background: var(--success); }

        .notification-content {
            flex: 1;
        }

        .notification-content p {
            margin-bottom: 0.2rem;
            font-size: 0.9rem;
        }

        .notification-time {
            color: #999;
            font-size: 0.7rem;
        }

        /* Weather Widget */
        .weather-widget {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .weather-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .weather-temp {
            font-size: 2.5rem;
            font-weight: bold;
        }

        .weather-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            font-size: 0.9rem;
        }

        .weather-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Market Prices */
        .market-prices {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 0;
            border-bottom: 1px solid var(--light);
        }

        .price-item:last-child {
            border-bottom: none;
        }

        .price-change {
            padding: 0.2rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .change-positive { background: var(--success); color: white; }
        .change-negative { background: var(--danger); color: white; }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .action-btn {
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            color: white;
            border: none;
            padding: 1.2rem 1rem;
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(123, 193, 66, 0.3);
        }

        .action-btn i {
            font-size: 1.8rem;
        }

        .action-btn span {
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: 1fr;
                padding: 1rem;
                margin-top: 70px;
            }

            .sidebar {
                display: none;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .welcome-section h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="dashboard-header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-seedling"></i>
                AgriSage
            </div>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-name">Hello, <?php echo htmlspecialchars($user_name); ?>! 👋</div>
                    <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
                </div>
                <button class="logout-btn" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </header>

    <!-- Main Dashboard -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="user-profile">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($user_name); ?></div>
                <div class="profile-role">Farmer</div>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="nav-item"><a href="crops.php"><i class="fas fa-leaf"></i> My Crops</a></li>
                <li class="nav-item"><a href="scan.php"><i class="fas fa-camera"></i> Scan Crop</a></li>
                <li class="nav-item"><a href="drone.php"><i class="fas fa-drone"></i> Drone Services</a></li>
                <li class="nav-item"><a href="marketplace.php"><i class="fas fa-shopping-cart"></i> Marketplace</a></li>
                <li class="nav-item"><a href="analytics.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
                <li class="nav-item"><a href="weather.php"><i class="fas fa-cloud-sun"></i> Weather</a></li>
                <li class="nav-item"><a href="profile.php"><i class="fas fa-user-cog"></i> Profile</a></li>
                <li class="nav-item"><a href="support.php"><i class="fas fa-headset"></i> Support</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Welcome Section -->
            <section class="welcome-section">
                <h1>Welcome to Your Farm Dashboard</h1>
                <p>Monitor your crops, access smart insights, and manage your farm efficiently</p>
            </section>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $stats['total_crops']; ?></div>
                        <div class="stat-label">Total Crops</div>
                    </div>
                    <div class="stat-trend">+12%</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $stats['recent_scans']; ?></div>
                        <div class="stat-label">Recent Scans</div>
                    </div>
                    <div class="stat-trend">+5%</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $stats['disease_crops']; ?></div>
                        <div class="stat-label">Disease Detected</div>
                    </div>
                    <div class="stat-trend" style="background: var(--danger);">-8%</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-drone"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $stats['pending_drone']; ?></div>
                        <div class="stat-label">Pending Drone Services</div>
                    </div>
                    <div class="stat-trend" style="background: var(--warning);">+2</div>
                </div>
            </div>

            <!-- Analytics Grid -->
            <div class="analytics-grid">
                <!-- Left Column - Charts and Activities -->
                <div class="left-column">
                    <!-- Crop Health Chart -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <h2><i class="fas fa-chart-pie"></i> Crop Health Overview</h2>
                            <a href="analytics.php" class="view-all">View Details</a>
                        </div>
                        <div class="health-chart">
                            <canvas id="healthChart"></canvas>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <h2><i class="fas fa-history"></i> Recent Activities</h2>
                            <a href="activities.php" class="view-all">View All</a>
                        </div>
                        <ul class="activity-list">
                            <?php if (count($activities) > 0): ?>
                                <?php foreach ($activities as $activity): ?>
                                    <li class="activity-item">
                                        <div class="activity-icon" style="background: <?php echo $activity['activity_type'] == 'scan' ? '#7bc142' : ($activity['activity_type'] == 'login' ? '#667eea' : '#ffa502'); ?>">
                                            <i class="fas fa-<?php echo $activity['activity_type'] == 'scan' ? 'camera' : ($activity['activity_type'] == 'login' ? 'sign-in-alt' : 'seedling'); ?>"></i>
                                        </div>
                                        <div class="activity-info">
                                            <h4><?php echo htmlspecialchars($activity['summary']); ?></h4>
                                            <p><?php echo ucfirst($activity['activity_type']); ?> activity</p>
                                        </div>
                                        <div class="activity-time">
                                            <?php echo date('H:i', strtotime($activity['created_at'])); ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="activity-item">
                                    <div class="activity-info">
                                        <p>No recent activities</p>
                                    </div>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <!-- Right Column - Info Widgets -->
                <div class="right-column">
                    <!-- Quick Stats -->
                    <div class="info-container">
                        <div class="info-header">
                            <h2><i class="fas fa-tachometer-alt"></i> Quick Stats</h2>
                        </div>
                        <div class="quick-stats">
                            <div class="quick-stat-item">
                                <div class="quick-stat-icon icon-total">
                                    <i class="fas fa-leaf"></i>
                                </div>
                                <div class="quick-stat-info">
                                    <div class="quick-stat-value"><?php echo $stats['total_crops']; ?> Crops</div>
                                    <div class="quick-stat-label">Total Managed</div>
                                </div>
                            </div>
                            <div class="quick-stat-item">
                                <div class="quick-stat-icon icon-healthy">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="quick-stat-info">
                                    <div class="quick-stat-value"><?php echo $stats['healthy_crops']; ?> Healthy</div>
                                    <div class="quick-stat-label">No issues detected</div>
                                </div>
                            </div>
                            <div class="quick-stat-item">
                                <div class="quick-stat-icon icon-diseased">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </div>
                                <div class="quick-stat-info">
                                    <div class="quick-stat-value"><?php echo $stats['disease_crops']; ?> Issues</div>
                                    <div class="quick-stat-label">Need attention</div>
                                </div>
                            </div>
                            <div class="quick-stat-item">
                                <div class="quick-stat-icon icon-pending">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="quick-stat-info">
                                    <div class="quick-stat-value"><?php echo $stats['pending_drone']; ?> Pending</div>
                                    <div class="quick-stat-label">Drone services</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Weather Widget -->
                    <div class="weather-widget">
                        <div class="weather-header">
                            <div>
                                <h3><i class="fas fa-map-marker-alt"></i> Farm Location</h3>
                                <p>Current Weather</p>
                            </div>
                            <div class="weather-temp"><?php echo $weather_data['temperature']; ?>°C</div>
                        </div>
                        <div class="weather-details">
                            <div class="weather-detail">
                                <i class="fas fa-tint"></i>
                                <span>Humidity: <?php echo $weather_data['humidity']; ?>%</span>
                            </div>
                            <div class="weather-detail">
                                <i class="fas fa-wind"></i>
                                <span>Wind: <?php echo $weather_data['wind_speed']; ?> km/h</span>
                            </div>
                            <div class="weather-detail">
                                <i class="fas fa-cloud-rain"></i>
                                <span>Rain: <?php echo $weather_data['rain_chance']; ?>%</span>
                            </div>
                            <div class="weather-detail">
                                <i class="fas fa-sun"></i>
                                <span>Partly Cloudy</span>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications -->
                    <div class="info-container">
                        <div class="info-header">
                            <h2><i class="fas fa-bell"></i> Notifications</h2>
                            <a href="notifications.php" class="view-all">View All</a>
                        </div>
                        <ul class="notification-list">
                            <?php foreach ($notifications as $notification): ?>
                                <li class="notification-item">
                                    <div class="notification-icon icon-<?php echo $notification['type']; ?>">
                                        <?php 
                                        $icon = 'fas fa-info-circle';
                                        if ($notification['type'] == 'weather') $icon = 'fas fa-sun';
                                        if ($notification['type'] == 'market') $icon = 'fas fa-shopping-cart';
                                        if ($notification['type'] == 'alert') $icon = 'fas fa-exclamation-triangle';
                                        ?>
                                        <i class="<?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="notification-content">
                                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                        <div class="notification-time">
                                            <?php echo date('M j, g:i A', strtotime($notification['created_at'])); ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Market Prices -->
                    <div class="market-prices">
                        <div class="info-header">
                            <h2><i class="fas fa-chart-line"></i> Market Prices</h2>
                            <a href="market.php" class="view-all">View All</a>
                        </div>
                        <?php foreach ($market_prices as $item): ?>
                            <div class="price-item">
                                <div>
                                    <strong><?php echo $item['crop']; ?></strong>
                                    <div style="font-size: 0.8rem; color: #666;">Per quintal</div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-weight: bold; color: var(--primary);">$<?php echo $item['price']; ?></div>
                                    <div class="price-change <?php echo $item['change'] >= 0 ? 'change-positive' : 'change-negative'; ?>">
                                        <?php echo $item['change'] >= 0 ? '+' : ''; ?><?php echo $item['change']; ?>%
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="chart-container">
                <div class="chart-header">
                    <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                </div>
                <div class="quick-actions">
                    <a href="scan.php" class="action-btn">
                        <i class="fas fa-camera"></i>
                        <span>Scan Crop</span>
                    </a>
                    <a href="drone.php" class="action-btn">
                        <i class="fas fa-drone"></i>
                        <span>Book Drone</span>
                    </a>
                    <a href="marketplace.php" class="action-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Marketplace</span>
                    </a>
                    <a href="analytics.php" class="action-btn">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                    <a href="support.php" class="action-btn">
                        <i class="fas fa-headset"></i>
                        <span>Get Support</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }

        // Initialize Charts
        document.addEventListener('DOMContentLoaded', function() {
            // Crop Health Chart
            const healthCtx = document.getElementById('healthChart').getContext('2d');
            const healthChart = new Chart(healthCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Healthy Crops', 'Diseased Crops'],
                    datasets: [{
                        data: [<?php echo $stats['healthy_crops']; ?>, <?php echo $stats['disease_crops']; ?>],
                        backgroundColor: ['#2ed573', '#ff4757'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    },
                    cutout: '70%'
                }
            });

            // Auto-refresh every 30 seconds
            setInterval(() => {
                // You can implement live data updates here
                console.log('Refreshing dashboard data...');
            }, 30000);
        });

        // Add smooth animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.stat-card, .chart-container, .info-container').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>