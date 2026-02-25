<?php
/**
 * seed_all_tables.php - Seeds ALL 20 tables with 10 realistic entries each.
 * Locations are clustered within 20km of SIMATS, Chennai (12.82, 80.04).
 * Run: http://localhost/petnexa_API/seed_all_tables.php
 */

header('Content-Type: application/json');
require_once 'db.php';

set_time_limit(60);
$results = [];

// ---------------------------------------------------------------------------
// Helper: random lat/lng within ~20km of center (12.82, 80.04)
// ---------------------------------------------------------------------------
function randLat() { return round(12.72 + (mt_rand(0, 2000) / 10000), 6); } // 12.72 – 12.92
function randLng() { return round(79.94 + (mt_rand(0, 2000) / 10000), 6); } // 79.94 – 80.14

// ---------------------------------------------------------------------------
// Disable FK checks
// ---------------------------------------------------------------------------
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// ---------------------------------------------------------------------------
// 1. USERS (10) – 3 Buyers, 3 Sellers, 2 Doctors, 2 Spa Owners
// ---------------------------------------------------------------------------
$users = [
    // Buyers (1-3)
    ['Ananya Sharma',   'ananya@test.com',   '9876543210', 'BuyerPass1',  'BUYER',     '12, Anna Nagar, Chennai'],
    ['Rohan Mehta',     'rohan@test.com',    '9876543211', 'BuyerPass2',  'BUYER',     '45, T. Nagar, Chennai'],
    ['Priya Krishnan',  'priya@test.com',    '9876543212', 'BuyerPass3',  'BUYER',     '78, Adyar, Chennai'],
    // Sellers (4-6)
    ['Vikram Pets',     'vikram@test.com',   '9876543213', 'SellerPass1', 'SELLER',    '23, Porur, Chennai'],
    ['Lakshmi Animals', 'lakshmi@test.com',  '9876543214', 'SellerPass2', 'SELLER',    '56, Ambattur, Chennai'],
    ['Karthik Store',   'karthik@test.com',  '9876543215', 'SellerPass3', 'SELLER',    '89, Avadi, Chennai'],
    // Doctors (7-8)
    ['Dr. Ramesh Kumar','ramesh@test.com',   '9876543216', 'DoctorPass1', 'DOCTOR',    '34, Vadapalani, Chennai'],
    ['Dr. Meera Nair',  'meera@test.com',    '9876543217', 'DoctorPass2', 'DOCTOR',    '67, Guindy, Chennai'],
    // Spa Owners (9-10)
    ['Suresh Iyer',     'suresh@test.com',   '9876543218', 'SpaPass1',   'SPA_OWNER', '90, Nungambakkam, Chennai'],
    ['Deepa Rajan',     'deepa@test.com',    '9876543219', 'SpaPass2',   'SPA_OWNER', '11, Velachery, Chennai'],
];

$stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password_hash, role, address, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($users as $u) {
    $lat = randLat(); $lng = randLng();
    $stmt->bind_param("ssssssdd", $u[0], $u[1], $u[2], $u[3], $u[4], $u[5], $lat, $lng);
    $stmt->execute();
}
$stmt->close();
$results['users'] = '10 inserted';

// ---------------------------------------------------------------------------
// 2. BUYER_PROFILES (3) – users 1-3
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO buyer_profiles (user_id, upi_id) VALUES
    (1, 'ananya@upi'), (2, 'rohan@upi'), (3, 'priya@upi')");
$results['buyer_profiles'] = '3 inserted';

// ---------------------------------------------------------------------------
// 3. SELLER_PROFILES (3) – users 4-6
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO seller_profiles (user_id, shop_name, seller_type, upi_id) VALUES
    (4, 'Vikram Pet Shop',    'SHOP',       'vikram@upi'),
    (5, 'Lakshmi Pet World',  'SHOP',       'lakshmi@upi'),
    (6, '',                   'INDIVIDUAL', 'karthik@upi')");
$results['seller_profiles'] = '3 inserted';

// ---------------------------------------------------------------------------
// 4. DOCTOR_PROFILES (2) – users 7-8
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO doctor_profiles (user_id, qualification, specialization, experience, hospital, languages, upi_id) VALUES
    (7, 'MVSc',  'Surgery',            7, 'Chennai Vet Hospital',  'English, Tamil, Hindi', 'ramesh@upi'),
    (8, 'BVSc',  'General Practice',   4, 'Guindy Animal Clinic',  'English, Tamil',        'meera@upi')");
$results['doctor_profiles'] = '2 inserted';

// ---------------------------------------------------------------------------
// 5. SPA_PROFILES (2) – users 9-10
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO spa_profiles (user_id, spa_name, services_offered, upi_id, rating, total_reviews) VALUES
    (9,  'Pawsome Spa',     'Bathing, Grooming, Hair Cut, Nail Trimming',         'suresh@upi', 4.5, 12),
    (10, 'Happy Tails Spa', 'Bathing, De-shedding, Teeth Cleaning, Flea Treatment','deepa@upi',  4.2, 8)");
$results['spa_profiles'] = '2 inserted';

// ---------------------------------------------------------------------------
// 6. PETS (10) – owned by sellers 4-6
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO pets (seller_id, pet_name, species, breed, age, gender, color, price, description, availability_status) VALUES
    (4, 'Bruno',    'Dog',  'Golden Retriever',   2, 'MALE',   'Golden',       15000.00, 'Friendly and playful golden retriever',    'AVAILABLE'),
    (4, 'Whiskers', 'Cat',  'Persian',            1, 'FEMALE', 'White',        12000.00, 'Beautiful long-haired Persian cat',         'AVAILABLE'),
    (4, 'Rocky',    'Dog',  'German Shepherd',     3, 'MALE',   'Black & Tan', 20000.00, 'Well-trained GSD, great guard dog',         'AVAILABLE'),
    (5, 'Luna',     'Cat',  'Siamese',            2, 'FEMALE', 'Cream',        10000.00, 'Elegant Siamese with blue eyes',            'AVAILABLE'),
    (5, 'Max',      'Dog',  'Labrador Retriever', 1, 'MALE',   'Chocolate',   13000.00, 'Energetic and loving Lab puppy',            'AVAILABLE'),
    (5, 'Coco',     'Dog',  'Pomeranian',         2, 'FEMALE', 'Orange',       8000.00,  'Cute and fluffy Pomeranian',                'RESERVED'),
    (6, 'Charlie',  'Dog',  'Beagle',             1, 'MALE',   'Tricolor',    11000.00, 'Curious and merry Beagle puppy',            'AVAILABLE'),
    (6, 'Bella',    'Cat',  'Maine Coon',         3, 'FEMALE', 'Tabby',       18000.00, 'Gentle giant, very affectionate',            'AVAILABLE'),
    (6, 'Buddy',    'Dog',  'Shih Tzu',           2, 'MALE',   'White & Brown',9000.00, 'Adorable lap dog, great companion',         'SOLD'),
    (4, 'Simba',    'Cat',  'Bengal',             1, 'MALE',   'Spotted',     22000.00, 'Exotic Bengal cat with wild markings',       'AVAILABLE')");
$results['pets'] = '10 inserted';

// ---------------------------------------------------------------------------
// 7. PET_IMAGES (10) – one per pet
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO pet_images (pet_id, image_url) VALUES
    (1, 'uploads/pets/golden_retriever.jpg'),
    (2, 'uploads/pets/persian_cat.jpg'),
    (3, 'uploads/pets/german_shepherd.jpg'),
    (4, 'uploads/pets/siamese_cat.jpg'),
    (5, 'uploads/pets/labrador.jpg'),
    (6, 'uploads/pets/pomeranian.jpg'),
    (7, 'uploads/pets/beagle.jpg'),
    (8, 'uploads/pets/maine_coon.jpg'),
    (9, 'uploads/pets/shih_tzu.jpg'),
    (10,'uploads/pets/bengal_cat.jpg')");
$results['pet_images'] = '10 inserted';

// ---------------------------------------------------------------------------
// 8. CERTIFICATES (10) – mix of types across pets
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO certificates (pet_id, issued_by, certificate_type, certificate_file, issued_date, expiry_date, notes) VALUES
    (1, 7, 'VACCINATION', 'uploads/certs/cert_1.pdf', '2025-06-15', '2026-06-15', 'Rabies vaccination complete'),
    (2, 8, 'HEALTH',      'uploads/certs/cert_2.pdf', '2025-07-20', '2026-07-20', 'General health check passed'),
    (3, 7, 'VACCINATION', 'uploads/certs/cert_3.pdf', '2025-05-10', '2026-05-10', 'Parvo & Distemper combo'),
    (4, 8, 'HEALTH',      'uploads/certs/cert_4.pdf', '2025-08-01', '2026-08-01', 'FIV/FeLV negative'),
    (5, 7, 'VACCINATION', 'uploads/certs/cert_5.pdf', '2025-09-12', '2026-09-12', 'DHPP vaccination'),
    (6, 8, 'LICENSE',     'uploads/certs/cert_6.pdf', '2025-04-05', '2027-04-05', 'Municipal pet license'),
    (7, 7, 'VACCINATION', 'uploads/certs/cert_7.pdf', '2025-10-18', '2026-10-18', 'Rabies + Bordetella'),
    (8, 8, 'HEALTH',      'uploads/certs/cert_8.pdf', '2025-11-22', '2026-11-22', 'Deworming completed'),
    (9, 7, 'LICENSE',     'uploads/certs/cert_9.pdf', '2025-03-30', '2027-03-30', 'City registration license'),
    (10,8, 'VACCINATION', 'uploads/certs/cert_10.pdf','2025-12-01', '2026-12-01', 'FVRCP vaccine for cats')");
$results['certificates'] = '10 inserted';

// ---------------------------------------------------------------------------
// 9. DOCTOR_SERVICES (10) – 5 per doctor
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO doctor_services (doctor_id, service_name, description, price, duration) VALUES
    (7, 'General Checkup',    'Complete physical examination',          500.00,  '30 min'),
    (7, 'Vaccination',        'Core vaccines for dogs and cats',       800.00,  '20 min'),
    (7, 'Surgery Consult',    'Pre-surgical evaluation',              1500.00,  '45 min'),
    (7, 'Dental Cleaning',    'Professional teeth cleaning',          2000.00,  '60 min'),
    (7, 'Emergency Care',     'Urgent medical attention',             3000.00,  '60 min'),
    (8, 'Wellness Exam',      'Annual wellness screening',             600.00,  '30 min'),
    (8, 'Deworming',          'Internal parasite treatment',           400.00,  '15 min'),
    (8, 'Skin Treatment',     'Dermatological exam and treatment',    1200.00,  '40 min'),
    (8, 'X-Ray',              'Digital radiography',                  1800.00,  '30 min'),
    (8, 'Blood Work',         'Complete blood panel analysis',        1000.00,  '20 min')");
$results['doctor_services'] = '10 inserted';

// ---------------------------------------------------------------------------
// 10. SPA_SERVICES (10) – 5 per spa
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO spa_services (spa_id, service_name, description, duration_minutes, duration, price) VALUES
    (1, 'Full Bath',         'Complete bathing with shampoo & conditioner',  45, '45 min', 500.00),
    (1, 'Grooming Deluxe',   'Full body grooming with styling',             90, '90 min', 1200.00),
    (1, 'Nail Trimming',     'Safe nail clipping and filing',               20, '20 min', 300.00),
    (1, 'Hair Cut',          'Breed-specific hair styling',                 60, '60 min', 800.00),
    (1, 'Flea Treatment',    'Anti-flea bath and prevention spray',         40, '40 min', 700.00),
    (2, 'Basic Bath',        'Quick bath with premium shampoo',             30, '30 min', 400.00),
    (2, 'De-shedding',       'Deep coat de-shedding treatment',             60, '60 min', 900.00),
    (2, 'Teeth Cleaning',    'Oral hygiene and breath freshening',          30, '30 min', 600.00),
    (2, 'Ear Cleaning',      'Gentle ear cleaning and inspection',          15, '15 min', 250.00),
    (2, 'Spa Package',       'Bath + grooming + nail trim combo',          120, '120 min',1800.00)");
$results['spa_services'] = '10 inserted';

// ---------------------------------------------------------------------------
// 11. DOCTOR_APPOINTMENTS (10) – buyers booking doctors
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO doctor_appointments (pet_id, doctor_id, buyer_id, appointment_date, booking_time, service_name, visit_type, consultation_status, treatment_charge) VALUES
    (1, 7, 1, '2026-02-25 10:00:00', '10:00 AM', 'General Checkup',  'clinic',  'BOOKED',    500.00),
    (2, 8, 1, '2026-02-25 11:30:00', '11:30 AM', 'Wellness Exam',    'clinic',  'BOOKED',    600.00),
    (3, 7, 2, '2026-02-26 09:00:00', '09:00 AM', 'Surgery Consult',  'clinic',  'BOOKED',   1500.00),
    (5, 8, 2, '2026-02-26 14:00:00', '02:00 PM', 'Deworming',        'home',    'BOOKED',    400.00),
    (7, 7, 3, '2026-02-27 10:30:00', '10:30 AM', 'Vaccination',      'clinic',  'BOOKED',    800.00),
    (1, 7, 1, '2026-01-15 10:00:00', '10:00 AM', 'Dental Cleaning',  'clinic',  'COMPLETED', 2000.00),
    (4, 8, 2, '2026-01-20 15:00:00', '03:00 PM', 'Skin Treatment',   'clinic',  'COMPLETED', 1200.00),
    (6, 7, 3, '2026-01-25 11:00:00', '11:00 AM', 'Emergency Care',   'home',    'COMPLETED', 3000.00),
    (8, 8, 1, '2026-02-01 09:30:00', '09:30 AM', 'Blood Work',       'clinic',  'COMPLETED', 1000.00),
    (5, 7, 3, '2026-02-10 16:00:00', '04:00 PM', 'General Checkup',  'home',    'CANCELLED',  500.00)");
$results['doctor_appointments'] = '10 inserted';

// ---------------------------------------------------------------------------
// 12. SPA_BOOKINGS (10) – buyers booking spas
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO spa_bookings (spa_id, pet_id, service_id, buyer_id, user_id, booking_date, booking_time, status, total_amount, pet_name, pet_type, booking_status, payment_status) VALUES
    (1, 1, 1, 1, 1, '2026-02-25 10:00:00', '10:00:00', 'pending',   500.00,  'Bruno',    'Dog', 'BOOKED',    'PENDING'),
    (1, 3, 2, 2, 2, '2026-02-25 11:00:00', '11:00:00', 'pending',  1200.00,  'Rocky',    'Dog', 'BOOKED',    'PENDING'),
    (2, 2, 6, 1, 1, '2026-02-26 09:00:00', '09:00:00', 'pending',   400.00,  'Whiskers', 'Cat', 'BOOKED',    'PENDING'),
    (2, 5, 7, 3, 3, '2026-02-26 14:00:00', '14:00:00', 'pending',   900.00,  'Max',      'Dog', 'BOOKED',    'PENDING'),
    (1, 7, 4, 3, 3, '2026-02-27 10:00:00', '10:00:00', 'confirmed', 800.00,  'Charlie',  'Dog', 'BOOKED',    'SUCCESS'),
    (1, 1, 1, 1, 1, '2026-01-15 10:00:00', '10:00:00', 'completed', 500.00,  'Bruno',    'Dog', 'COMPLETED', 'SUCCESS'),
    (2, 4, 8, 2, 2, '2026-01-20 15:00:00', '15:00:00', 'completed', 600.00,  'Luna',     'Cat', 'COMPLETED', 'SUCCESS'),
    (1, 6, 5, 2, 2, '2026-01-25 11:00:00', '11:00:00', 'completed', 700.00,  'Coco',     'Dog', 'COMPLETED', 'SUCCESS'),
    (2, 8, 10,1, 1, '2026-02-01 09:00:00', '09:00:00', 'completed',1800.00,  'Bella',    'Cat', 'COMPLETED', 'SUCCESS'),
    (1, 5, 3, 3, 3, '2026-02-10 16:00:00', '16:00:00', 'cancelled', 300.00,  'Max',      'Dog', 'CANCELLED', 'FAILED')");
$results['spa_bookings'] = '10 inserted';

// ---------------------------------------------------------------------------
// 13. CHAT_MESSAGES (10) – conversations between buyers and sellers
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO chat_messages (sender_id, receiver_id, message_text, message_type, is_read, is_delivered) VALUES
    (1, 4, 'Hi, is Bruno still available?',                     'text', 1, 1),
    (4, 1, 'Yes! Bruno is available. Would you like to visit?', 'text', 1, 1),
    (1, 4, 'Can you share more photos?',                        'text', 1, 1),
    (2, 5, 'What is the price for Max?',                        'text', 1, 1),
    (5, 2, 'Max is priced at Rs 13000. Negotiable.',            'text', 1, 1),
    (3, 6, 'Is Charlie vaccinated?',                            'text', 1, 1),
    (6, 3, 'Yes, fully vaccinated with certificate.',           'text', 0, 1),
    (2, 4, 'Can I visit to see Rocky this weekend?',            'text', 0, 1),
    (1, 5, 'Do you deliver pets to Anna Nagar?',                'text', 0, 1),
    (5, 1, 'Yes we can arrange delivery within Chennai.',       'text', 0, 1)");
$results['chat_messages'] = '10 inserted';

// ---------------------------------------------------------------------------
// 14. NOTIFICATIONS (10) – various types
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO notifications (user_id, title, message, type, reference_id, is_read) VALUES
    (1, 'Appointment Confirmed',  'Your appointment with Dr. Ramesh on Feb 25 is confirmed.',   'appointment', 1, 0),
    (2, 'Booking Confirmed',      'Your spa booking at Pawsome Spa is confirmed.',               'booking',     2, 0),
    (7, 'New Appointment',        'Ananya has booked a General Checkup on Feb 25.',              'appointment', 1, 0),
    (9, 'New Spa Booking',        'Rohan has booked Grooming Deluxe at your spa.',               'booking',     2, 0),
    (4, 'New Message',            'You have a new message from Ananya about Bruno.',             'chat',        1, 1),
    (1, 'Pet Listed',             'Simba (Bengal) has been newly listed by Vikram Pets.',         'pet',        10, 0),
    (3, 'Payment Success',        'Payment of Rs 800 for Charlie grooming is successful.',       'payment',     5, 0),
    (8, 'Appointment Cancelled',  'Priya cancelled the General Checkup appointment.',            'appointment',10, 0),
    (10,'Spa Review',             'Ananya left a 5 star review for Happy Tails Spa.',            'review',      1, 0),
    (2, 'Welcome!',               'Welcome to Petnexa! Explore pets, doctors, and spas nearby.', 'system',     NULL, 1)");
$results['notifications'] = '10 inserted';

// ---------------------------------------------------------------------------
// 15. REVIEWS (10) – buyers reviewing sellers / doctors
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO reviews (reviewer_id, target_user_id, rating, comment) VALUES
    (1, 4, 5, 'Excellent pet shop! Bruno is healthy and playful.'),
    (2, 5, 4, 'Good service. Max was well taken care of.'),
    (3, 6, 5, 'Charlie is adorable! Very transparent seller.'),
    (1, 7, 5, 'Dr. Ramesh is very knowledgeable and caring.'),
    (2, 8, 4, 'Dr. Meera explained everything clearly.'),
    (3, 7, 5, 'Emergency care was prompt. Thank you doctor!'),
    (1, 5, 4, 'Lakshmi has a great collection of pets.'),
    (2, 4, 5, 'Rocky is a stunning dog. Vikram is trustworthy.'),
    (3, 8, 4, 'Skin treatment worked wonders for Luna.'),
    (1, 6, 3, 'Decent experience but delivery was slow.')");
$results['reviews'] = '10 inserted';

// ---------------------------------------------------------------------------
// 16. SPA_REVIEWS (10) – buyers reviewing spas
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO spa_reviews (spa_id, user_id, rating, review_text) VALUES
    (1, 1, 5, 'Bruno loved the bath! Came back smelling amazing.'),
    (1, 2, 4, 'Rocky grooming was excellent. Took a bit long.'),
    (1, 3, 5, 'Great service and friendly staff at Pawsome Spa.'),
    (2, 1, 4, 'Whiskers was handled gently. Good cat spa!'),
    (2, 2, 5, 'Luna enjoyed the de-shedding treatment.'),
    (2, 3, 4, 'Teeth cleaning was thorough. Will visit again.'),
    (1, 1, 5, 'Nail trimming was quick and painless for Bruno.'),
    (1, 2, 3, 'Had to wait 20 mins past appointment time.'),
    (2, 3, 5, 'Spa package was worth every rupee!'),
    (2, 1, 4, 'Ear cleaning was gentle. Staff is professional.')");
$results['spa_reviews'] = '10 inserted';

// ---------------------------------------------------------------------------
// 17. PET_TRANSACTIONS (10) – buy/sell transactions
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO pet_transactions (pet_id, buyer_id, seller_id, amount, payment_method, payment_status) VALUES
    (9,  2, 6, 9000.00,  'UPI',     'SUCCESS'),
    (1,  1, 4, 15000.00, 'UPI',     'PENDING'),
    (4,  2, 5, 10000.00, 'UPI',     'SUCCESS'),
    (5,  3, 5, 13000.00, 'Cash',    'SUCCESS'),
    (7,  3, 6, 11000.00, 'UPI',     'BOOKED'),
    (2,  1, 4, 12000.00, 'UPI',     'CONFIRMED'),
    (3,  2, 4, 20000.00, 'Cash',    'PENDING'),
    (6,  1, 5, 8000.00,  'UPI',     'SUCCESS'),
    (8,  3, 6, 18000.00, 'UPI',     'BOOKED'),
    (10, 2, 4, 22000.00, 'Cash',    'PENDING')");
$results['pet_transactions'] = '10 inserted';

// ---------------------------------------------------------------------------
// 18. SAVED_PETS (10) – buyers saving favorite pets
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO saved_pets (buyer_id, pet_id) VALUES
    (1, 1), (1, 3), (1, 10),
    (2, 2), (2, 5), (2, 7),
    (3, 4), (3, 6), (3, 8), (3, 9)");
$results['saved_pets'] = '10 inserted';

// ---------------------------------------------------------------------------
// 19. TYPING_STATUS (10) – recent typing indicators
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO typing_status (user_id, recipient_id, is_typing) VALUES
    (1, 4, 0), (4, 1, 0),
    (2, 5, 0), (5, 2, 0),
    (3, 6, 0), (6, 3, 0),
    (1, 5, 0), (5, 1, 0),
    (2, 4, 0), (4, 2, 0)");
$results['typing_status'] = '10 inserted';

// ---------------------------------------------------------------------------
// 20. AI_BREED_DETECTION (10)
// ---------------------------------------------------------------------------
$conn->query("INSERT INTO ai_breed_detection (user_id, uploaded_image, predicted_breed, confidence_score) VALUES
    (1, 'uploads/ai/detect_1.jpg', 'Golden Retriever',   95.50),
    (2, 'uploads/ai/detect_2.jpg', 'Persian Cat',        92.30),
    (3, 'uploads/ai/detect_3.jpg', 'German Shepherd',    97.10),
    (1, 'uploads/ai/detect_4.jpg', 'Siamese Cat',        89.80),
    (2, 'uploads/ai/detect_5.jpg', 'Labrador Retriever', 93.60),
    (3, 'uploads/ai/detect_6.jpg', 'Pomeranian',         88.40),
    (1, 'uploads/ai/detect_7.jpg', 'Beagle',             91.20),
    (2, 'uploads/ai/detect_8.jpg', 'Maine Coon',         94.70),
    (3, 'uploads/ai/detect_9.jpg', 'Shih Tzu',           87.90),
    (1, 'uploads/ai/detect_10.jpg','Bengal Cat',          96.10)");
$results['ai_breed_detection'] = '10 inserted';

// ---------------------------------------------------------------------------
// Re-enable FK checks
// ---------------------------------------------------------------------------
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// ---------------------------------------------------------------------------
// Output summary
// ---------------------------------------------------------------------------
echo json_encode([
    'success' => true,
    'message' => 'All 20 tables seeded successfully!',
    'tables'  => $results
], JSON_PRETTY_PRINT);

$conn->close();
?>
