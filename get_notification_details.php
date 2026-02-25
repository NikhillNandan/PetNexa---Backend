<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';
$reference_id = isset($_GET['reference_id']) ? intval($_GET['reference_id']) : 0;

if (empty($type) || $reference_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'type and reference_id are required']);
    exit;
}

try {
    $conn_pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $details = [];

    switch ($type) {
        case 'appointment':
            $stmt = $conn_pdo->prepare("
                SELECT da.appointment_id, da.appointment_date, da.booking_time,
                       da.service_name, da.consultation_status, da.treatment_notes,
                       da.treatment_charge,
                       u_owner.full_name AS owner_name, u_owner.phone AS owner_phone,
                       u_doc.full_name AS doctor_name,
                       COALESCE(p.pet_name, 'No Pet Specified') AS pet_name,
                       COALESCE(p.breed, 'Unknown') AS breed,
                       ds.price AS base_price
                FROM doctor_appointments da
                LEFT JOIN users u_owner ON da.buyer_id = u_owner.user_id
                LEFT JOIN users u_doc ON da.doctor_id = u_doc.user_id
                LEFT JOIN pets p ON da.pet_id = p.pet_id
                LEFT JOIN doctor_services ds ON ds.doctor_id = da.doctor_id AND ds.service_name = da.service_name
                WHERE da.appointment_id = ?
                LIMIT 1
            ");
            $stmt->execute([$reference_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $date = date('D, d M Y', strtotime($row['appointment_date']));
                $time = $row['booking_time'] ?: date('h:i A', strtotime($row['appointment_date']));
                $base = floatval($row['base_price'] ?: 0);
                $extra = floatval($row['treatment_charge'] ?: 0);
                $total = $base + $extra;

                $details = [
                    ['label' => 'Doctor', 'value' => $row['doctor_name'] ?: 'N/A'],
                    ['label' => 'Pet', 'value' => $row['pet_name']],
                    ['label' => 'Breed', 'value' => $row['breed']],
                    ['label' => 'Date', 'value' => $date],
                    ['label' => 'Time', 'value' => $time],
                    ['label' => 'Services', 'value' => $row['service_name'] ?: 'General Consultation'],
                    ['label' => 'Status', 'value' => ucfirst(strtolower($row['consultation_status'] ?: 'Pending'))],
                    ['label' => 'Total Amount', 'value' => '₹' . number_format($total, 2)],
                    ['label' => 'Owner', 'value' => $row['owner_name'] ?: 'N/A'],
                    ['label' => 'Owner Phone', 'value' => $row['owner_phone'] ?: 'N/A']
                ];

                if (!empty($row['treatment_notes'])) {
                    $details[] = ['label' => 'Treatment Notes', 'value' => $row['treatment_notes']];
                }
            }
            break;

        case 'booking':
            $stmt = $conn_pdo->prepare("
                SELECT sb.booking_id, sb.booking_date, sb.booking_time,
                       COALESCE(NULLIF(sb.booking_status, ''), sb.status, 'BOOKED') as resolved_status,
                       COALESCE(sb.total_amount, ss.price, 0) as total_amount,
                       ss.service_name, ss.duration_minutes,
                       u_buyer.full_name AS customer_name, u_buyer.phone AS customer_phone,
                       sp.spa_name,
                       COALESCE(p.pet_name, 'No Pet Specified') AS pet_name,
                       COALESCE(p.breed, 'Unknown') AS breed
                FROM spa_bookings sb
                LEFT JOIN spa_services ss ON sb.service_id = ss.service_id
                LEFT JOIN users u_buyer ON sb.buyer_id = u_buyer.user_id
                LEFT JOIN spa_profiles sp ON sb.spa_id = sp.spa_id
                LEFT JOIN pets p ON sb.pet_id = p.pet_id
                WHERE sb.booking_id = ?
                LIMIT 1
            ");
            $stmt->execute([$reference_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // Format date
                $raw_date = $row['booking_date'];
                $formatted_date = 'Not scheduled';
                if ($raw_date && $raw_date !== '0000-00-00 00:00:00') {
                    $ts = strtotime($raw_date);
                    if ($ts) $formatted_date = date('D, d M Y', $ts);
                }

                // Format time
                $raw_time = $row['booking_time'];
                $formatted_time = 'N/A';
                if ($raw_time && $raw_time !== '00:00:00') {
                    $formatted_time = date('h:i A', strtotime($raw_time));
                }

                $details = [
                    ['label' => 'Spa', 'value' => $row['spa_name'] ?: 'N/A'],
                    ['label' => 'Service', 'value' => $row['service_name'] ?: 'N/A'],
                    ['label' => 'Pet', 'value' => $row['pet_name']],
                    ['label' => 'Breed', 'value' => $row['breed']],
                    ['label' => 'Date', 'value' => $formatted_date],
                    ['label' => 'Time', 'value' => $formatted_time],
                    ['label' => 'Duration', 'value' => intval($row['duration_minutes']) . ' minutes'],
                    ['label' => 'Fee', 'value' => '₹' . number_format(floatval($row['total_amount']), 2)],
                    ['label' => 'Status', 'value' => ucfirst(strtolower($row['resolved_status']))],
                    ['label' => 'Customer', 'value' => $row['customer_name'] ?: 'N/A'],
                    ['label' => 'Customer Phone', 'value' => $row['customer_phone'] ?: 'N/A']
                ];
            }
            break;

        case 'order':
            $stmt = $conn_pdo->prepare("
                SELECT po.order_id, po.quantity, po.total_amount, po.status, po.created_at,
                       p.pet_name, p.breed, p.price,
                       u_buyer.full_name AS buyer_name, u_buyer.phone AS buyer_phone,
                       u_seller.full_name AS seller_name
                FROM pet_orders po
                LEFT JOIN pets p ON po.pet_id = p.pet_id
                LEFT JOIN users u_buyer ON po.buyer_id = u_buyer.user_id
                LEFT JOIN users u_seller ON po.seller_id = u_seller.user_id
                WHERE po.order_id = ?
                LIMIT 1
            ");
            $stmt->execute([$reference_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $details = [
                    ['label' => 'Pet', 'value' => $row['pet_name'] ?: 'N/A'],
                    ['label' => 'Breed', 'value' => $row['breed'] ?: 'N/A'],
                    ['label' => 'Price', 'value' => '₹' . number_format(floatval($row['price'] ?: 0), 2)],
                    ['label' => 'Total Amount', 'value' => '₹' . number_format(floatval($row['total_amount'] ?: 0), 2)],
                    ['label' => 'Status', 'value' => ucfirst(strtolower($row['status'] ?: 'Pending'))],
                    ['label' => 'Buyer', 'value' => $row['buyer_name'] ?: 'N/A'],
                    ['label' => 'Seller', 'value' => $row['seller_name'] ?: 'N/A'],
                    ['label' => 'Ordered On', 'value' => $row['created_at'] ?: 'N/A']
                ];
            }
            break;

        default:
            break;
    }

    if (empty($details)) {
        echo json_encode(['success' => false, 'error' => 'No details found for this reference']);
    } else {
        echo json_encode(['success' => true, 'details' => $details]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
