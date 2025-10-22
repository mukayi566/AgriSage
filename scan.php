<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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

// Handle file upload and scan
$scan_result = null;
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['crop_image']) && $_FILES['crop_image']['error'] === UPLOAD_ERR_OK) {
        try {
            // Get form data
            $name = $_POST['name'] ?? '';
            $crop_type = $_POST['crop_type'] ?? '';
            $yield = $_POST['yield'] ?? null;
            $height = $_POST['height'] ?? null;
            $growth_period = $_POST['growth_period'] ?? null;
            $description = $_POST['description'] ?? '';
            $growing_conditions = $_POST['growing_conditions'] ?? '';
            
            // Validate inputs
            if (empty($name) || empty($crop_type)) {
                $error_message = "Crop name and type are required.";
            } else {
                // File upload handling
                $upload_dir = 'uploads/crops/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = pathinfo($_FILES['crop_image']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid() . '_' . $user_id . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;
                
                // Check file type
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array(strtolower($file_extension), $allowed_types)) {
                    $error_message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
                } elseif ($_FILES['crop_image']['size'] > 5 * 1024 * 1024) { // 5MB limit
                    $error_message = "File size must be less than 5MB.";
                } elseif (move_uploaded_file($_FILES['crop_image']['tmp_name'], $file_path)) {
                    
                    // Simulate disease detection (Replace this with your actual ML model)
                    $disease_status = simulateDiseaseDetection($file_path, $crop_type);
                    
                    // Insert crop record into database WITHOUT image_path
                    $insert_query = "
                        INSERT INTO crops 
                        (user_id, name, crop_type, yield, height, growth_period, description, growing_conditions, scan_date, disease_status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
                    ";
                    
                    $stmt = $db->prepare($insert_query);
                    $stmt->execute([
                        $user_id, 
                        $name, 
                        $crop_type,
                        $yield, 
                        $height, 
                        $growth_period, 
                        $description, 
                        $growing_conditions, 
                        $disease_status
                    ]);
                    
                    $crop_id = $db->lastInsertId();
                    
                    // Record activity
                    $activity_query = "
                        INSERT INTO activities (user_id, activity_type, summary) 
                        VALUES (?, 'scan', ?)
                    ";
                    $activity_stmt = $db->prepare($activity_query);
                    $activity_stmt->execute([$user_id, "Scanned {$crop_type} crop '{$name}' for disease detection"]);
                    
                    // Prepare scan result WITHOUT image_path
                    $scan_result = [
                        'id' => $crop_id,
                        'name' => $name,
                        'crop_type' => $crop_type,
                        'yield' => $yield,
                        'height' => $height,
                        'growth_period' => $growth_period,
                        'description' => $description,
                        'growing_conditions' => $growing_conditions,
                        'disease_status' => $disease_status,
                        'scan_date' => date('Y-m-d H:i:s'),
                        'confidence' => rand(85, 98) // Simulated confidence percentage
                    ];
                    
                    $success_message = "Crop scanned successfully!";
                    
                } else {
                    $error_message = "Failed to upload image. Please try again.";
                }
            }
        } catch (Exception $e) {
            $error_message = "An error occurred: " . $e->getMessage();
        }
    } else {
        $error_message = "Please select an image to scan.";
    }
}

// Simulate disease detection function (Replace with your actual ML model)
function simulateDiseaseDetection($image_path, $crop_type) {
    // This is a simulation - replace with actual ML model integration
    $diseases = [
        'Wheat' => ['healthy', 'rust', 'powdery_mildew', 'leaf_blight'],
        'Maize' => ['healthy', 'leaf_blight', 'rust', 'gray_leaf_spot'],
        'Rice' => ['healthy', 'blast', 'brown_spot', 'bacterial_blight'],
        'Tomato' => ['healthy', 'early_blight', 'late_blight', 'mosaic_virus'],
        'Potato' => ['healthy', 'late_blight', 'early_blight', 'scab'],
        'Soybean' => ['healthy', 'rust', 'bacterial_blight', 'powdery_mildew'],
        'Corn' => ['healthy', 'leaf_blight', 'rust', 'gray_leaf_spot']
    ];
    
    $available_diseases = $diseases[$crop_type] ?? ['healthy', 'unknown_disease'];
    
    // 70% chance of healthy, 30% chance of disease
    if (rand(1, 100) <= 70) {
        return 'healthy';
    } else {
        $disease_index = rand(1, count($available_diseases) - 1);
        return $available_diseases[$disease_index];
    }
}

// Get recent scans for the user using the correct field names
$recent_scans_query = "
    SELECT id, name, crop_type, yield, height, growth_period, disease_status, scan_date 
    FROM crops 
    WHERE user_id = ? 
    ORDER BY scan_date DESC 
    LIMIT 5
";
$recent_scans_stmt = $db->prepare($recent_scans_query);
$recent_scans_stmt->execute([$user_id]);
$recent_scans = $recent_scans_stmt->fetchAll(PDO::FETCH_ASSOC);

// Common crop types for dropdown
$crop_types = ['Wheat', 'Maize', 'Rice', 'Tomato', 'Potato', 'Soybean', 'Corn', 'Barley', 'Oats', 'Sunflower', 'Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Crop - AgriSage</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

        .back-btn {
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
            text-decoration: none;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Main Layout */
        .scan-container {
            max-width: 1200px;
            margin: 100px auto 2rem;
            padding: 0 2rem;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: #666;
            font-size: 1.1rem;
        }

        /* Scan Section */
        .scan-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        @media (max-width: 768px) {
            .scan-section {
                grid-template-columns: 1fr;
            }
        }

        /* Cards */
        .upload-card, .results-card, .recent-scans {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light);
        }

        .card-header h2 {
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

        /* Upload Area */
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .upload-area:hover {
            border-color: var(--accent);
            background: var(--light);
        }

        .upload-area i {
            font-size: 3rem;
            color: var(--accent);
            margin-bottom: 1rem;
        }

        .upload-area h3 {
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .upload-area p {
            color: #666;
            font-size: 0.9rem;
        }

        #image-preview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            display: none;
            margin: 1rem auto;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Results Section */
        .scan-result {
            text-align: center;
            padding: 1rem;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .status-healthy {
            background: #d4edda;
            color: #155724;
        }

        .status-diseased {
            background: #f8d7da;
            color: #721c24;
        }

        .result-details {
            background: var(--light);
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1.5rem;
        }

        .result-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #ddd;
        }

        .result-item:last-child {
            border-bottom: none;
        }

        /* Recent Scans List */
        .scan-list {
            list-style: none;
        }

        .scan-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--light);
            transition: background 0.3s ease;
        }

        .scan-item:hover {
            background: var(--light);
        }

        .scan-item:last-child {
            border-bottom: none;
        }

        .scan-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            margin-right: 1rem;
        }

        .icon-healthy { background: var(--success); }
        .icon-diseased { background: var(--danger); }

        .scan-info {
            flex: 1;
        }

        .scan-info h4 {
            margin-bottom: 0.2rem;
            color: var(--dark);
            font-size: 0.95rem;
        }

        .scan-info p {
            color: #666;
            font-size: 0.8rem;
        }

        .scan-time {
            color: #999;
            font-size: 0.7rem;
        }

        /* Buttons */
        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--secondary));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(123, 193, 66, 0.4);
        }

        .btn-block {
            width: 100%;
            justify-content: center;
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .hidden {
            display: none;
        }

        /* Crop Suggestions */
        .crop-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .crop-suggestion {
            background: var(--light);
            border: 1px solid #ddd;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .crop-suggestion:hover {
            background: var(--accent);
            color: white;
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
                </div>
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="scan-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-camera"></i> Scan Crop for Disease</h1>
            <p>Upload a photo of your crop to detect potential diseases and get recommendations</p>
        </div>

        <!-- Alerts -->
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <!-- Scan Section -->
        <div class="scan-section">
            <!-- Upload Form -->
            <div class="upload-card">
                <div class="card-header">
                    <h2><i class="fas fa-upload"></i> Upload & Scan</h2>
                </div>
                <form id="scan-form" method="POST" enctype="multipart/form-data">
                    <div class="upload-area" onclick="document.getElementById('crop_image').click()">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <h3>Upload Crop Image</h3>
                        <p>Click to select or drag and drop</p>
                        <p class="text-muted">Supports JPG, PNG, GIF - Max 5MB</p>
                        <img id="image-preview" src="" alt="Preview">
                    </div>
                    <input type="file" id="crop_image" name="crop_image" accept="image/*" hidden required>
                    
                    <div class="form-group">
                        <label for="name"><i class="fas fa-tag"></i> Crop Name *</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="e.g., Wheat Field A" required>
                    </div>

                    <div class="form-group">
                        <label for="crop_type"><i class="fas fa-leaf"></i> Crop Type *</label>
                        <select id="crop_type" name="crop_type" class="form-control" required>
                            <option value="">Select Crop Type</option>
                            <?php foreach ($crop_types as $type): ?>
                                <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="yield"><i class="fas fa-chart-line"></i> Yield (tons/ha)</label>
                            <input type="number" id="yield" name="yield" class="form-control" placeholder="e.g., 3.5" step="0.1" min="0">
                        </div>

                        <div class="form-group">
                            <label for="height"><i class="fas fa-arrows-alt-v"></i> Height (meters)</label>
                            <input type="number" id="height" name="height" class="form-control" placeholder="e.g., 0.8" step="0.1" min="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="growth_period"><i class="fas fa-calendar-alt"></i> Growth Period (days)</label>
                        <input type="number" id="growth_period" name="growth_period" class="form-control" placeholder="e.g., 120" min="1">
                    </div>

                    <div class="form-group">
                        <label for="description"><i class="fas fa-file-alt"></i> Description</label>
                        <textarea id="description" name="description" class="form-control" placeholder="Describe the crop, variety, or any special characteristics..." rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="growing_conditions"><i class="fas fa-seedling"></i> Growing Conditions</label>
                        <textarea id="growing_conditions" name="growing_conditions" class="form-control" placeholder="Soil type, climate conditions, irrigation methods..." rows="2"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> Scan for Diseases
                    </button>
                </form>
            </div>

            <!-- Results Section -->
            <div class="results-card" id="results-section" style="<?php echo $scan_result ? '' : 'display: none;' ?>">
                <div class="card-header">
                    <h2><i class="fas fa-clipboard-check"></i> Scan Results</h2>
                </div>

                <?php if ($scan_result): ?>
                    <div class="scan-result">
                        <div class="status-indicator <?php echo $scan_result['disease_status'] === 'healthy' ? 'status-healthy' : 'status-diseased'; ?>">
                            <i class="fas fa-<?php echo $scan_result['disease_status'] === 'healthy' ? 'check' : 'exclamation-triangle'; ?>"></i>
                            <?php echo ucfirst(str_replace('_', ' ', $scan_result['disease_status'])); ?>
                        </div>

                        <div class="result-details">
                            <div class="result-item">
                                <span>Crop Name:</span>
                                <strong><?php echo htmlspecialchars($scan_result['name']); ?></strong>
                            </div>
                            <div class="result-item">
                                <span>Crop Type:</span>
                                <strong><?php echo htmlspecialchars($scan_result['crop_type']); ?></strong>
                            </div>
                            <?php if ($scan_result['yield']): ?>
                                <div class="result-item">
                                    <span>Yield:</span>
                                    <strong><?php echo htmlspecialchars($scan_result['yield']); ?> tons/ha</strong>
                                </div>
                            <?php endif; ?>
                            <?php if ($scan_result['height']): ?>
                                <div class="result-item">
                                    <span>Height:</span>
                                    <strong><?php echo htmlspecialchars($scan_result['height']); ?> meters</strong>
                                </div>
                            <?php endif; ?>
                            <?php if ($scan_result['growth_period']): ?>
                                <div class="result-item">
                                    <span>Growth Period:</span>
                                    <strong><?php echo htmlspecialchars($scan_result['growth_period']); ?> days</strong>
                                </div>
                            <?php endif; ?>
                            <div class="result-item">
                                <span>Scan Date:</span>
                                <strong><?php echo date('M j, Y g:i A', strtotime($scan_result['scan_date'])); ?></strong>
                            </div>
                            <div class="result-item">
                                <span>Confidence:</span>
                                <strong><?php echo $scan_result['confidence']; ?>%</strong>
                            </div>
                        </div>

                        <?php if ($scan_result['description']): ?>
                            <div style="margin-top: 1rem; padding: 1rem; background: var(--light); border-radius: 8px;">
                                <h4><i class="fas fa-file-alt"></i> Description</h4>
                                <p><?php echo htmlspecialchars($scan_result['description']); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($scan_result['growing_conditions']): ?>
                            <div style="margin-top: 1rem; padding: 1rem; background: var(--light); border-radius: 8px;">
                                <h4><i class="fas fa-seedling"></i> Growing Conditions</h4>
                                <p><?php echo htmlspecialchars($scan_result['growing_conditions']); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($scan_result['disease_status'] !== 'healthy'): ?>
                            <div style="margin-top: 1.5rem; padding: 1rem; background: #fff3cd; border-radius: 8px;">
                                <h4><i class="fas fa-exclamation-triangle"></i> Recommended Actions</h4>
                                <p style="margin-top: 0.5rem;">Consider consulting with an agricultural expert for treatment options.</p>
                                <a href="support.php" class="btn btn-primary" style="margin-top: 0.5rem;">
                                    <i class="fas fa-headset"></i> Get Expert Support
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="scan-result">
                        <p>Upload and scan a crop image to see results here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Scans -->
        <div class="recent-scans">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Recent Scans</h2>
                <a href="crops.php" class="view-all">View All</a>
            </div>

            <?php if (count($recent_scans) > 0): ?>
                <ul class="scan-list">
                    <?php foreach ($recent_scans as $scan): ?>
                        <li class="scan-item">
                            <div class="scan-icon <?php echo $scan['disease_status'] === 'healthy' ? 'icon-healthy' : 'icon-diseased'; ?>">
                                <i class="fas fa-<?php echo $scan['disease_status'] === 'healthy' ? 'check' : 'exclamation-triangle'; ?>"></i>
                            </div>
                            <div class="scan-info">
                                <h4><?php echo htmlspecialchars($scan['name']); ?></h4>
                                <p>
                                    <?php echo htmlspecialchars($scan['crop_type']); ?> - 
                                    <?php echo ucfirst(str_replace('_', ' ', $scan['disease_status'])); ?>
                                    <?php if ($scan['yield']) echo " • " . $scan['yield'] . " tons/ha"; ?>
                                    <?php if ($scan['height']) echo " • " . $scan['height'] . "m"; ?>
                                </p>
                            </div>
                            <div class="scan-time">
                                <?php echo date('M j', strtotime($scan['scan_date'])); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 2rem;">No recent scans found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Image preview functionality (kept for upload preview but not stored in database)
        document.getElementById('crop_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('image-preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        // Drag and drop functionality
        const uploadArea = document.querySelector('.upload-area');
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight() {
            uploadArea.style.borderColor = 'var(--accent)';
            uploadArea.style.background = 'var(--light)';
        }

        function unhighlight() {
            uploadArea.style.borderColor = '#ddd';
            uploadArea.style.background = 'transparent';
        }

        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            document.getElementById('crop_image').files = files;
            
            // Trigger change event
            const event = new Event('change');
            document.getElementById('crop_image').dispatchEvent(event);
        }

        // Show results section when form is submitted
        document.getElementById('scan-form').addEventListener('submit', function() {
            document.getElementById('results-section').style.display = 'block';
        });

        // Auto-fill common crop data based on crop type
        document.getElementById('crop_type').addEventListener('change', function(e) {
            const cropType = e.target.value;
            const yieldField = document.getElementById('yield');
            const heightField = document.getElementById('height');
            const growthField = document.getElementById('growth_period');
            const descField = document.getElementById('description');
            const conditionsField = document.getElementById('growing_conditions');

            // Auto-fill based on crop type
            if (cropType === 'Wheat') {
                if (!yieldField.value) yieldField.value = '3.5';
                if (!heightField.value) heightField.value = '0.8';
                if (!growthField.value) growthField.value = '120';
                if (!descField.value) descField.value = 'Common cereal grain grown for its seed, a worldwide staple food';
                if (!conditionsField.value) conditionsField.value = 'Temperate climate, well-drained soil, full sun, pH 6.0-7.5';
            } else if (cropType === 'Maize' || cropType === 'Corn') {
                if (!yieldField.value) yieldField.value = '6.8';
                if (!heightField.value) heightField.value = '2.5';
                if (!growthField.value) growthField.value = '90';
                if (!descField.value) descField.value = 'Also known as corn, used for human consumption and animal feed';
                if (!conditionsField.value) conditionsField.value = 'Warm climate, well-drained soil, full sun, pH 5.8-7.0';
            } else if (cropType === 'Rice') {
                if (!yieldField.value) yieldField.value = '4.2';
                if (!heightField.value) heightField.value = '1.2';
                if (!growthField.value) growthField.value = '110';
                if (!descField.value) descField.value = 'Staple food for large part of the world population, especially Asia';
                if (!conditionsField.value) conditionsField.value = 'Tropical climate, flooded fields, high humidity, pH 5.5-6.5';
            }
        });
    </script>
</body>
</html>