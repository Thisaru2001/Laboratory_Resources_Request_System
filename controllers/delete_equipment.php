<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Auth check - Allow HOD and Technical Officer
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['hod', 'technical_officer'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get JSON body
$input = json_decode(file_get_contents('php://input'), true);
$code = trim($input['code'] ?? '');

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Equipment code is required']);
    exit;
}

try {
    // Check if equipment exists
    $result = Database::search("SELECT id, name, is_hod_checked FROM equipment WHERE code = ?", "s", [$code]);

    if (!$result || $result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Equipment not found']);
        exit;
    }

    $equipment = $result->fetch_assoc();
    $equipmentId = $equipment['id'];

    // Check if already deactivated
    if ((int)$equipment['is_hod_checked'] === 0) {
        echo json_encode(['success' => false, 'message' => 'Equipment is already deactivated']);
        exit;
    }

    // Check for active bookings - use book_equipment and join with reservation
    $bookingResult = Database::search(
        "SELECT COUNT(*) as count FROM book_equipment be 
         JOIN reservation r ON be.reservation_id = r.id
         WHERE be.equipment_id = ? 
         AND r.technical_officer_id IS NOT NULL
         AND NOT EXISTS (SELECT 1 FROM reject_reason rr WHERE rr.reservation_id = r.id)",
        "i",
        [$equipmentId]
    );

    if ($bookingResult && $bookingResult->num_rows > 0) {
        $row = $bookingResult->fetch_assoc();
        if ((int)$row['count'] > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Cannot deactivate: ' . $row['count'] . ' active booking(s) exist'
            ]);
            exit;
        }
    }

    // Soft delete
    Database::iud("UPDATE equipment SET is_hod_checked = 0 WHERE id = ?", "i", [$equipmentId]);

    // Verify by re-querying (don't trust UPDATE return value)
    $verifyResult = Database::search("SELECT is_hod_checked FROM equipment WHERE id = ?", "i", [$equipmentId]);
    $verifyRow = $verifyResult->fetch_assoc();

    if ((int)$verifyRow['is_hod_checked'] === 0) {
        echo json_encode(['success' => true, 'message' => 'Equipment deactivated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>