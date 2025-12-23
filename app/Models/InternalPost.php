<?php

class InternalPost extends Model
{
    // Get active posts (trang_thai = 1) ordered by newest first
    public static function getActive($limit = 50, $offset = 0)
    {
        $db = self::db();
        $limit = (int)$limit;
        $offset = (int)$offset;
        $sql = "SELECT * FROM internal_posts WHERE trang_thai = 1 ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get images for a post ordered by sort_order
    public static function getImages($postId)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT * FROM internal_post_images WHERE internal_post_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([(int)$postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get single post by id, include images
    public static function getById(int $id)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT * FROM internal_posts WHERE id = ? LIMIT 1");
        $stmt->execute([(int)$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$post) return null;

        // attach images
        try {
            $stmt2 = $db->prepare("SELECT id, image_path, sort_order FROM internal_post_images WHERE internal_post_id = ? ORDER BY sort_order ASC, id ASC");
            $stmt2->execute([(int)$id]);
            $imgs = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            $post['images'] = $imgs ?: [];
        } catch (Exception $e) {
            $post['images'] = [];
        }

        return $post;
    }

    public static function countActive($search = null)
    {
        $db = self::db();
        $params = [];
        $sql = "SELECT COUNT(*) FROM internal_posts WHERE trang_thai = 1";
        if ($search) {
            $sql .= " AND (tieu_de LIKE ? OR noi_dung LIKE ?)";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function deleteById(int $id)
    {
        $db = self::db();

        try {
            $db->beginTransaction();

            // fetch images to unlink
            $stmt = $db->prepare("SELECT image_path FROM internal_post_images WHERE internal_post_id = ?");
            $stmt->execute([$id]);
            $imgs = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // delete image rows
            $stmtDelImgs = $db->prepare("DELETE FROM internal_post_images WHERE internal_post_id = ?");
            $stmtDelImgs->execute([$id]);

            // delete post
            $stmtDel = $db->prepare("DELETE FROM internal_posts WHERE id = ?");
            $stmtDel->execute([$id]);

            $db->commit();

            // unlink files outside transaction
            foreach ($imgs as $p) {
                if (!$p) continue;
                $path = __DIR__ . '/../../public/' . ltrim($p, '/');
                if (file_exists($path) && is_file($path)) @unlink($path);
            }

            return true;
        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            $msg = date('Y-m-d H:i:s') . " - InternalPost::deleteById error: " . $e->getMessage() . "\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/internal_post_error.log', $msg, FILE_APPEND);
            return false;
        }
    }

    // Convenience: get first image path or null
    public static function getFirstImagePath($postId)
    {
        $images = self::getImages($postId);
        if (!$images) return null;
        return $images[0]['image_path'] ?? null;
    }

    public static function create(array $data)
    {
        $db = self::db();
        $sql = "INSERT INTO internal_posts (user_id, tieu_de, noi_dung, trang_thai, ma_hien_thi, created_at, updated_at) VALUES (:user_id, :tieu_de, :noi_dung, :trang_thai, :ma_hien_thi, :created_at, :updated_at)";
        $now = date('Y-m-d H:i:s');
        $params = [
            ':user_id' => $data['user_id'] ?? null,
            ':tieu_de' => $data['tieu_de'] ?? null,
            ':noi_dung' => $data['noi_dung'] ?? null,
            ':trang_thai' => isset($data['trang_thai']) ? (int)$data['trang_thai'] : 1,
            ':ma_hien_thi' => '',
            ':created_at' => $now,
            ':updated_at' => $now,
        ];

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $id = (int)$db->lastInsertId();

            // generate ma_hien_thi like TTNB001
            $ma = 'TTNB' . str_pad($id, 3, '0', STR_PAD_LEFT);
            $stmt2 = $db->prepare("UPDATE internal_posts SET ma_hien_thi = :ma WHERE id = :id");
            $stmt2->execute([':ma' => $ma, ':id' => $id]);

            return $id;
        } catch (PDOException $e) {
            $msg = date('Y-m-d H:i:s') . " - InternalPost::create error: " . $e->getMessage() . " Params: " . json_encode($params) . "\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/internal_post_error.log', $msg, FILE_APPEND);
            return false;
        }
    }

    public static function addImages(int $postId, array $imagePaths)
    {
        if (empty($imagePaths)) return false;
        $db = self::db();
        $stmt = $db->prepare("INSERT INTO internal_post_images (internal_post_id, image_path, sort_order, created_at) VALUES (?, ?, ?, NOW())");
        $order = 1;
        foreach ($imagePaths as $p) {
            $stmt->execute([(int)$postId, $p, $order]);
            $order++;
        }
        return true;
    }

    public static function update(int $id, array $data)
    {
        $db = self::db();
        $sql = "UPDATE internal_posts SET tieu_de = :tieu_de, noi_dung = :noi_dung, trang_thai = :trang_thai, updated_at = :updated_at WHERE id = :id";
        $params = [
            ':tieu_de' => $data['tieu_de'] ?? null,
            ':noi_dung' => $data['noi_dung'] ?? null,
            ':trang_thai' => isset($data['trang_thai']) ? (int)$data['trang_thai'] : 1,
            ':updated_at' => date('Y-m-d H:i:s'),
            ':id' => $id
        ];
        try {
            $stmt = $db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $msg = date('Y-m-d H:i:s') . " - InternalPost::update error: " . $e->getMessage() . " Params: " . json_encode($params) . "\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/internal_post_error.log', $msg, FILE_APPEND);
            return false;
        }
    }

    public static function deleteImageById(int $imageId)
    {
        $db = self::db();
        try {
            // fetch path
            $stmt = $db->prepare("SELECT image_path FROM internal_post_images WHERE id = ? LIMIT 1");
            $stmt->execute([$imageId]);
            $path = $stmt->fetchColumn();

            // delete row
            $stmtDel = $db->prepare("DELETE FROM internal_post_images WHERE id = ?");
            $ok = $stmtDel->execute([$imageId]);

            // unlink file if exists
            if ($ok && $path) {
                $fsPath = __DIR__ . '/../../public/' . ltrim($path, '/');
                if (file_exists($fsPath) && is_file($fsPath)) @unlink($fsPath);
            }
            return (bool)$ok;
        } catch (Exception $e) {
            $msg = date('Y-m-d H:i:s') . " - InternalPost::deleteImageById error: " . $e->getMessage() . "\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/internal_post_error.log', $msg, FILE_APPEND);
            return false;
        }
    }
}
