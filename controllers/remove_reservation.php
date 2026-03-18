<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if student is logged in
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$student_id = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0;

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$reservation_id = $input['reservation_id'] ?? '';

if (empty($reservation_id)) {
    echo json_encode(['success' => false, 'message' => 'Reservation ID is required']);
    exit;
}

try {
    // Check if reservation exists, belongs to student, and has no supervisor/technical officer assigned
    $check_query = "SELECT id, supervisor_id, technical_officer_id 
                    FROM reservation 
                    WHERE reservation_id = ? 
                    AND student_id = ? 
                    AND supervisor_id IS NULL 
                    AND technical_officer_id IS NULL";

    $check_result = Database::search($check_query, "si", [$reservation_id, $student_id]);

    if (!$check_result || $check_result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'You can only delete reservations that have not been assigned to any supervisor or technical officer'
        ]);
        exit;
    }

    $row = $check_result->fetch_assoc();
    $internal_id = $row['id'];




    try {
        // Check if there's a reject reason (though unlikely since no supervisor assigned)
        $reject_check = "SELECT id FROM reject_reason WHERE reservation_id = ?";
        $reject_result = Database::search($reject_check, "i", [$internal_id]);

        // Delete reject_reason if exists
        if ($reject_result && $reject_result->num_rows > 0) {
            $reject_row = $reject_result->fetch_assoc();
            Database::iud("DELETE FROM reject_reason WHERE id = ?", "i", [$reject_row['id']]);
        }

        // Delete from book_equipment
        Database::iud("DELETE FROM book_equipment WHERE reservation_id = ?", "i", [$internal_id]);

        // Delete the reservation
        $delete_result = Database::iud(
            "DELETE FROM reservation WHERE id = ? AND student_id = ? 
     AND supervisor_id IS NULL AND technical_officer_id IS NULL",
            "ii",
            [$internal_id, $student_id]
        );

        if ($delete_result) {
            echo json_encode(['success' => true, 'message' => 'Reservation cancelled successfully']);
        } else {
            throw new Exception('Failed to delete reservation');
        }
    } catch (Exception $e) {

        throw $e;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
