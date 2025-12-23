<?php

class DealPost extends Model
{
    public static function getList(int $limit = 20, int $offset = 0, ?string $search = null)
    {
        $db = self::db();
        $params = [];
        $sql = "SELECT dp.*, u.ho_ten AS author_name FROM deal_posts dp LEFT JOIN users u ON dp.user_id = u.id WHERE 1=1";
        if ($search) {
            $sql .= " AND (dp.noi_dung LIKE ?)";
            $like = '%' . $search . '%';
            $params[] = $like;
        }
        $sql .= " ORDER BY dp.created_at DESC LIMIT " . ((int)$limit) . " OFFSET " . ((int)$offset);

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // attach images for each post
        foreach ($posts as &$p) {
            $p['images'] = [];
            if (!empty($p['id'])) {
                $stmt2 = $db->prepare("SELECT image_path FROM deal_post_images WHERE deal_post_id = ? ORDER BY id ASC");
                $stmt2->execute([(int)$p['id']]);
                $imgs = $stmt2->fetchAll(PDO::FETCH_COLUMN);
                $p['images'] = $imgs ?: [];
            }
        }

        return $posts;
    }

    public static function getById(int $id)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT dp.*, u.ho_ten AS author_name FROM deal_posts dp LEFT JOIN users u ON dp.user_id = u.id WHERE dp.id = ? LIMIT 1");
        $stmt->execute([(int)$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$post) return null;

        $stmt2 = $db->prepare("SELECT image_path FROM deal_post_images WHERE deal_post_id = ? ORDER BY id ASC");
        $stmt2->execute([(int)$id]);
        $post['images'] = $stmt2->fetchAll(PDO::FETCH_COLUMN) ?: [];

        return $post;
    }

    public static function create(array $data)
    {
        $db = self::db();
        $sql = "INSERT INTO deal_posts (user_id, bat_dong_san_id, tieu_de, noi_dung, trang_thai, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $db->prepare($sql);
        $userId = $data['user_id'] ?? null;
        $bdsId = $data['bat_dong_san_id'] ?? null;
        $tieuDe = $data['tieu_de'] ?? null;
        $noiDung = $data['noi_dung'] ?? null;
        $trangThai = isset($data['trang_thai']) ? (int)$data['trang_thai'] : 1;
        $stmt->execute([$userId, $bdsId, $tieuDe, $noiDung, $trangThai]);
        return (int)$db->lastInsertId();
    }

    public static function addImages(int $dealPostId, array $imagePaths)
    {
        if (empty($imagePaths)) return false;
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO deal_post_images (deal_post_id, image_path, created_at) VALUES (?, ?, NOW())");
        foreach ($imagePaths as $p) {
            $stmt->execute([(int)$dealPostId, $p]);
        }
        return true;
    }
}
