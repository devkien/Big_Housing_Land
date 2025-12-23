<?php

class Collection extends Model
{
    // Return collections with item counts, optional search
    public static function allWithCount(?string $search = null)
    {
        $db = self::db();
        $params = [];

        $sql = "SELECT c.id, c.ten_bo_suu_tap, c.anh_dai_dien, c.mo_ta, c.is_default, c.trang_thai, COUNT(ci.id) AS item_count
                FROM collections c
                LEFT JOIN collection_items ci ON ci.collection_id = c.id";

        if ($search) {
            $sql .= " WHERE c.ten_bo_suu_tap LIKE ? OR c.mo_ta LIKE ?";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
        }

        $sql .= " GROUP BY c.id ORDER BY c.created_at DESC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $msg = date('Y-m-d H:i:s') . " - Collection::allWithCount error: " . $e->getMessage() . " SQL: " . $sql . " Params: " . json_encode($params) . "\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/collection_error.log', $msg, FILE_APPEND);
            return [];
        }
    }

    public static function create(array $data)
    {
        $db = self::db();
        $sql = "INSERT INTO collections (user_id, ten_bo_suu_tap, anh_dai_dien, mo_ta, is_default, trang_thai, created_at, updated_at)
                VALUES (:user_id, :ten, :anh, :mo_ta, :is_default, :trang_thai, :created_at, :updated_at)";
        $now = date('Y-m-d H:i:s');
        $params = [
            ':user_id' => $data['user_id'] ?? null,
            ':ten' => $data['ten_bo_suu_tap'] ?? null,
            ':anh' => $data['anh_dai_dien'] ?? null,
            ':mo_ta' => $data['mo_ta'] ?? null,
            ':is_default' => isset($data['is_default']) ? (int)$data['is_default'] : 0,
            ':trang_thai' => isset($data['trang_thai']) ? (int)$data['trang_thai'] : 1,
            ':created_at' => $now,
            ':updated_at' => $now,
        ];

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $db->lastInsertId();
        } catch (PDOException $e) {
            $msg = date('Y-m-d H:i:s') . " - Collection::create error: " . $e->getMessage() . " Params: " . json_encode($params) . "\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/collection_error.log', $msg, FILE_APPEND);
            return false;
        }
    }

    public static function updateName(int $id, string $name)
    {
        $db = self::db();
        $sql = "UPDATE collections SET ten_bo_suu_tap = :ten, updated_at = :updated_at WHERE id = :id";
        $params = [':ten' => $name, ':updated_at' => date('Y-m-d H:i:s'), ':id' => $id];
        try {
            $stmt = $db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $msg = date('Y-m-d H:i:s') . " - Collection::updateName error: " . $e->getMessage() . " Params: " . json_encode($params) . "\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/collection_error.log', $msg, FILE_APPEND);
            return false;
        }
    }

    public static function deleteById(int $id)
    {
        $db = self::db();
        $sql = "DELETE FROM collections WHERE id = :id";
        try {
            $stmt = $db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            $msg = date('Y-m-d H:i:s') . " - Collection::deleteById error: " . $e->getMessage() . " Params: {\"id\":$id}\n";
            @file_put_contents(__DIR__ . '/../../storage/logs/collection_error.log', $msg, FILE_APPEND);
            return false;
        }
    }
}
