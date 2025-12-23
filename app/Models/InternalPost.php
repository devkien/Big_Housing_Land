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

    // Convenience: get first image path or null
    public static function getFirstImagePath($postId)
    {
        $images = self::getImages($postId);
        if (!$images) return null;
        return $images[0]['image_path'] ?? null;
    }
}
