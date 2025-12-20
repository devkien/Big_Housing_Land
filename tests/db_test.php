<?php
chdir(__DIR__ . '/..');
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (!defined('BASE_PATH')) define('BASE_PATH', '/Big_Housing_Land');
if (!defined('BASE_URL')) define('BASE_URL', BASE_PATH);

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../app/Models/User.php';

try {
    $db = Database::connect();
    echo "DB connected\n";

    $phone = '0912345678';
    // use a unique phone to avoid duplicate key on repeated runs
    $phone = '0912' . rand(100000, 999999);
    $exists = User::findByPhone($phone);
    echo 'findByPhone: ' . ($exists ? 'FOUND' : 'NOT FOUND') . "\n";

    $data = [
        'so_dien_thoai' => $phone,
        'password' => password_hash('TestPass123', PASSWORD_BCRYPT),
        'ho_ten' => 'Test User',
        'nam_sinh' => '1990',
        'email' => 'test' . rand(1000,9999) . '@example.com',
        'gioi_tinh' => 'nam',
        'loai_tai_khoan' => 'nhan_vien',
        'quyen' => 'user',
        'trang_thai' => 1,
    ];

    // include a sample anh_cccd path to verify it's saved
    $data['anh_cccd'] = 'uploads/test-img-' . rand(1000,9999) . '.jpg';

    $ok = User::createWithRole($data);
    echo 'createWithRole returned: ' . ($ok ? 'true' : 'false') . "\n";

    if ($ok) {
        $last = $db->lastInsertId();
        echo 'Last insert id: ' . $last . "\n";
        $stmt = $db->prepare('SELECT id, so_dien_thoai, email, anh_cccd, trang_thai FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$last]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo 'Inserted row anh_cccd: ' . ($row['anh_cccd'] ?? 'NULL') . "\n";
    }
} catch (Exception $e) {
    echo 'Exception: ' . $e->getMessage() . "\n";
}
