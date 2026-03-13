<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION["user"])) {
    echo '<div class="text-center text-danger">Unauthorized</div>';
    exit();
}

$equipment_id = $_GET['id'] ?? 0;

if (!$equipment_id) {
    echo '<div class="text-center text-danger">Invalid equipment ID</div>';
    exit();
}

// Updated query with simultaneous_users
$query = "SELECT 
            e.id, 
            e.code, 
            e.name, 
            e.total_qty, 
            e.simultaneous_users,
            e.description, 
            e.sterilization_required,
            e.reservation_required, 
            e.added_datatime,
            e.image_path,
            GROUP_CONCAT(DISTINCT l.location SEPARATOR ', ') as locations
          FROM equipment e
          LEFT JOIN equipment_has_location ehl ON e.id = ehl.equipment_id
          LEFT JOIN location l ON ehl.location_id = l.id
          WHERE e.id = ?
          GROUP BY e.id";

$result = Database::search($query, "i", [$equipment_id]);

if ($result->num_rows === 0) {
    echo '<div class="text-center text-danger">Equipment not found</div>';
    exit();
}

$row = $result->fetch_assoc();

// Get location display
$locations = $row['locations'] ?? 'Not assigned';

// Handle image path
$image_url = 'https://cdn-icons-png.flaticon.com/512/2941/2941514.png'; // Default image

if (!empty($row['image_path'])) {
    // Clean the path
    $clean_path = str_replace('\\', '/', $row['image_path']);
    $clean_path = ltrim($clean_path, '/');
    
    // Check if file exists
    $full_path = $_SERVER['DOCUMENT_ROOT'] . '/LRRS/' . $clean_path;
    if (file_exists($full_path)) {
        $image_url = '/LRRS/' . $clean_path;
    }
}
?>

<style>
.equipment-detail-container {
    padding: 15px;
}

/* Image Section */
.equipment-image-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.equipment-detail-image {
    width: 180px;
    height: 180px;
    object-fit: contain;
    margin: 0 auto 15px;
    filter: drop-shadow(0 5px 10px rgba(0,0,0,0.1));
}

.equipment-title {
    font-size: 1.8rem;
    font-weight: 700;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1.2;
    margin-bottom: 5px;
}

.equipment-code {
    color: #6c757d;
    font-size: 1rem;
    background: white;
    padding: 4px 12px;
    border-radius: 50px;
    display: inline-block;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

/* Cards */
.info-card {
    background: white;
    border-radius: 14px;
    padding: 16px 18px;
    box-shadow: 0 3px 12px rgba(0,0,0,0.03);
    margin-bottom: 16px;
    border: 1px solid rgba(0,0,0,0.03);
    transition: all 0.2s ease;
}

.info-card:hover {
    box-shadow: 0 5px 18px rgba(34, 197, 94, 0.08);
    border-color: rgba(34, 197, 94, 0.2);
}

.info-card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #166534;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1.5px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 8px;
    letter-spacing: 0.3px;
}

.info-card-title i {
    color: #22c55e;
    font-size: 1.1rem;
}

.info-row {
    display: flex;
    margin-bottom: 10px;
    padding: 4px 0;
    border-bottom: 1px dashed #f5f5f5;
    font-size: 0.95rem;
}

.info-row:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.info-label {
    width: 120px;
    font-weight: 600;
    color: #495057;
    font-size: 0.9rem;
}

.info-value {
    flex: 1;
    color: #212529;
    font-weight: 500;
}

/* Badges */
.location-badge {
    display: inline-block;
    background: #e8f5e9;
    color: #166534;
    padding: 4px 10px;
    border-radius: 30px;
    margin: 2px 3px;
    font-size: 0.85rem;
    border: 1px solid #22c55e33;
    transition: all 0.2s;
}

.location-badge:hover {
    background: #22c55e;
    color: white;
}

.location-badge i {
    font-size: 0.8rem;
    margin-right: 3px;
}

/* Description Box */
.description-box {
    background: #f8f9fa;
    padding: 14px 16px;
    border-radius: 12px;
    color: #2c3e50;
    font-style: italic;
    border-left: 3px solid #22c55e;
    font-size: 0.95rem;
    line-height: 1.5;
}

/* Requirements Cards */
.requirement-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    background: #f8f9fa;
    border-radius: 12px;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.requirement-item:hover {
    background: white;
    border-color: #22c55e33;
    box-shadow: 0 3px 10px rgba(34, 197, 94, 0.1);
}

.requirement-item i {
    font-size: 1.4rem;
    width: 32px;
    text-align: center;
}

.requirement-label {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 2px;
}

.requirement-value {
    font-weight: 700;
    font-size: 1rem;
    color: #212529;
}

/* Quick Stats */
.quick-stats {
    display: flex;
    gap: 12px;
    margin: 15px 0 5px;
    flex-wrap: wrap;
}

.stat-pill {
    background: #f8f9fa;
    padding: 6px 15px;
    border-radius: 30px;
    font-size: 0.9rem;
    color: #495057;
    border: 1px solid #e9ecef;
}

.stat-pill strong {
    color: #166534;
    margin-right: 4px;
}

/* Responsive */
@media (max-width: 768px) {
    .equipment-detail-image {
        width: 140px;
        height: 140px;
    }
    
    .equipment-title {
        font-size: 1.5rem;
    }
    
    .info-label {
        width: 100px;
    }
}
</style>

<div class="equipment-detail-container">
    <div class="row g-4">
        <!-- Left Column - Image and Quick Info -->
        <div class="col-md-4">
            <div class="equipment-image-section">
                <img src="<?php echo $image_url; ?>" class="equipment-detail-image" alt="<?php echo htmlspecialchars($row['name']); ?>">
                <div class="equipment-title"><?php echo htmlspecialchars($row['name']); ?></div>
                <div class="equipment-code">
                    <i class="bi bi-upc-scan me-1"></i><?php echo htmlspecialchars($row['code']); ?>
                </div>
                
                <!-- Quick Stats -->
                <!-- <div class="quick-stats justify-content-center">
                    <span class="stat-pill">
                        <strong><?php echo $row['total_qty']; ?></strong> Units
                    </span>
                    <span class="stat-pill">
                        <strong><?php echo $row['simultaneous_users'] ?? '1'; ?></strong> Users
                    </span>
                </div> -->
            </div>
        </div>

        <!-- Right Column - Details -->
        <div class="col-md-8">
            <!-- Location Card -->
            <div class="info-card">
                <div class="info-card-title">
                    <i class="bi bi-geo-alt-fill"></i>
                    Lab Locations
                </div>
                <div class="info-row">
                    <span class="info-label">Available in:</span>
                    <span class="info-value">
                        <?php 
                        if ($locations !== 'Not assigned') {
                            $location_array = explode(', ', $locations);
                            foreach ($location_array as $loc) {
                                echo '<span class="location-badge"><i class="bi bi-building"></i>' . htmlspecialchars($loc) . '</span> ';
                            }
                        } else {
                            echo '<span class="text-muted fst-italic">Not assigned to any lab</span>';
                        }
                        ?>
                    </span>
                </div>
            </div>

            <!-- Two Column Layout for Specifications and Requirements -->
            <div class="row g-3">
                <div class="col-md-6">
                    <!-- Specifications Card -->
                    <div class="info-card h-100">
                        <div class="info-card-title">
                            <i class="bi bi-gear-fill"></i>
                            Specifications
                        </div>
                        <div class="info-row">
                            <span class="info-label">Total Quantity:</span>
                            <span class="info-value"><strong><?php echo $row['total_qty']; ?></strong> units</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Simultaneous Users:</span>
                            <span class="info-value"><strong><?php echo $row['simultaneous_users'] ?? '1'; ?></strong> users</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Added Date:</span>
                            <span class="info-value">
                                <i class="bi bi-calendar3 me-1 text-success"></i>
                                <?php echo date('M d, Y', strtotime($row['added_datatime'])); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <!-- Requirements Card -->
                    <div class="info-card h-100">
                        <div class="info-card-title">
                            <i class="bi bi-clipboard-check"></i>
                            Requirements
                        </div>
                        <div class="requirement-item mb-2">
                            <i class="bi bi-droplet <?php echo $row['sterilization_required'] == 'YES' ? 'text-success' : 'text-secondary'; ?>"></i>
                            <div>
                                <div class="requirement-label">Sterilization</div>
                                <div class="requirement-value"><?php echo $row['sterilization_required']; ?></div>
                            </div>
                        </div>
                        <div class="requirement-item">
                            <i class="bi bi-calendar-check <?php echo $row['reservation_required'] == 'YES' ? 'text-success' : 'text-secondary'; ?>"></i>
                            <div>
                                <div class="requirement-label">Reservation</div>
                                <div class="requirement-value"><?php echo $row['reservation_required']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Description Card -->
            <div class="info-card mt-3">
                <div class="info-card-title">
                    <i class="bi bi-chat-text-fill"></i>
                    Description
                </div>
                <?php if (!empty($row['description'])): ?>
                    <div class="description-box">
                        <i class="bi bi-quote me-1 text-success opacity-50"></i>
                        <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                    </div>
                <?php else: ?>
                    <div class="text-muted text-center py-2 fst-italic">
                        <i class="bi bi-emoji-neutral me-1"></i>
                        No description available
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>