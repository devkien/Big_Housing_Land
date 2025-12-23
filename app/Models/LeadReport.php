<?php

class LeadReport extends Model
{
    public static function create($data)
    {
        $db = self::db();
        $stmt = $db->prepare("
            INSERT INTO lead_reports (
                user_id,
                customer_id,
                note,
                status,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        
        return $stmt->execute([
            $data['user_id'],
            $data['customer_id'],
            $data['note'],
            $data['status'] ?? 'cho_duyet' // Mặc định trạng thái chờ duyệt
        ]);
    }

    public static function getAll($limit = 10, $offset = 0, $search = null)
    {
        $db = self::db();
        $sql = "SELECT lr.*, u.ho_ten as ten_nguoi_gui, c.ho_ten as ten_khach, c.so_dien_thoai as sdt_khach
                FROM lead_reports lr
                LEFT JOIN users u ON lr.user_id = u.id
                LEFT JOIN customers c ON lr.customer_id = c.id";
        
        $params = [];
        if ($search) {
            $sql .= " WHERE u.ho_ten LIKE ? OR c.ho_ten LIKE ? OR c.so_dien_thoai LIKE ?";
            $like = "%$search%";
            $params = [$like, $like, $like];
        }
        
        $sql .= " ORDER BY lr.created_at DESC LIMIT $limit OFFSET $offset";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countAll($search = null)
    {
        $db = self::db();
        $sql = "SELECT COUNT(*) FROM lead_reports lr
                LEFT JOIN users u ON lr.user_id = u.id
                LEFT JOIN customers c ON lr.customer_id = c.id";
        
        $params = [];
        if ($search) {
            $sql .= " WHERE u.ho_ten LIKE ? OR c.ho_ten LIKE ? OR c.so_dien_thoai LIKE ?";
            $like = "%$search%";
            $params = [$like, $like, $like];
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function findByIdWithDetails($id)
    {
        $db = self::db();
<<<<<<< Updated upstream
        $sql = "SELECT lr.*, 
                       u.ho_ten as ten_nguoi_gui, 
                       u.so_dien_thoai as sdt_nguoi_gui,
                       c.ho_ten as ten_khach, 
                       c.so_dien_thoai as sdt_khach,
                       c.nam_sinh as nam_sinh_khach,
                       c.cccd as cccd_khach
=======
        $sql = "SELECT lr.*, c.*, u.ho_ten AS sender_name, u.so_dien_thoai AS sender_phone
>>>>>>> Stashed changes
                FROM lead_reports lr
                LEFT JOIN users u ON lr.user_id = u.id
                LEFT JOIN customers c ON lr.customer_id = c.id
                WHERE lr.id = ?
                LIMIT 1";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);
        $report = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($report && !empty($report['customer_id'])) {
            $imgStmt = $db->prepare("SELECT image_path FROM customer_images WHERE customer_id = ?");
            $imgStmt->execute([$report['customer_id']]);
            $report['images'] = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        return $report;
    }
}