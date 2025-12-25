<?php

class Property extends Model
{
    // Create a new property and return the inserted ID (int) or false on failure
    public static function create(array $data)
    {
        $db = self::db();
        // Note: `created_at` has DB default; don't include it in explicit column list
        $stmt = $db->prepare(
            "INSERT INTO properties (
                user_id,
                ma_hien_thi,
                phong_ban,
                tieu_de,
                loai_bds,
                loai_kho,
                phap_ly,
                ma_so_so,
                ma_so_thue,
                dien_tich,
                don_vi_dien_tich,
                chieu_dai,
                chieu_rong,
                so_tang,
                gia_chao,
                trich_thuong_gia_tri,
                trich_thuong_don_vi,
                tinh_thanh,
                quan_huyen,
                xa_phuong,
                dia_chi_chi_tiet,
                trang_thai,
                mo_ta,
                is_visible
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        // Ensure ma_hien_thi exists (DB requires non-null)
        // Use a stronger random id (time + 8 random bytes => 16 hex chars) to reduce collision risk.
        $ma_hien_thi = $data['ma_hien_thi'] ?? null;

        // Prepare params - placeholder for ma_hien_thi will be filled below
        $params = [
            $data['user_id'] ?? null,
            null, // ma_hien_thi placeholder -> set before execute
            $data['phong_ban'] ?? null,
            $data['tieu_de'] ?? null,
            $data['loai_bds'] ?? null,
            $data['loai_kho'] ?? null,
            $data['phap_ly'] ?? null,
            $data['ma_so_so'] ?? null,
            $data['ma_so_thue'] ?? null,
            isset($data['dien_tich']) ? $data['dien_tich'] : null,
            $data['don_vi_dien_tich'] ?? null,
            $data['chieu_dai'] ?? null,
            $data['chieu_rong'] ?? null,
            $data['so_tang'] ?? null,
            isset($data['gia_chao']) ? $data['gia_chao'] : null,
            $data['trich_thuong_gia_tri'] ?? null,
            $data['trich_thuong_don_vi'] ?? null,
            $data['tinh_thanh'] ?? null,
            $data['quan_huyen'] ?? null,
            $data['xa_phuong'] ?? null,
            $data['dia_chi_chi_tiet'] ?? null,
            $data['trang_thai'] ?? null,
            $data['mo_ta'] ?? null,
            isset($data['is_visible']) ? (int)$data['is_visible'] : 1
        ];

        // Try to execute; if duplicate ma_hien_thi occurs (unique constraint), retry few times
        $tries = 0;
        $maxTries = 5;
        while ($tries < $maxTries) {
            if (empty($ma_hien_thi)) {
                $ma_hien_thi = 'P' . time() . bin2hex(random_bytes(8));
            }
            $params[1] = $ma_hien_thi; // fill ma_hien_thi param
            try {
                $ok = $stmt->execute($params);
                if ($ok) break;
            } catch (PDOException $e) {
                // SQLSTATE 23000 often indicates duplicate key; regenerate and retry
                if ($e->getCode() === '23000') {
                    $ma_hien_thi = null; // force regenerate
                    $tries++;
                    continue;
                }
                throw $e; // rethrow other DB exceptions
            }
            $tries++;
        }
        if (empty($ok)) return false;
        $dbh = self::db();
        return (int)$dbh->lastInsertId();
    }

    // Insert media rows for a property. $media is array of ['type'=>'image'|'video','path'=>string]
    public static function addMedia(int $propertyId, array $media)
    {
        if (empty($media)) return true;
        $db = self::db();
        $stmt = $db->prepare(
            "INSERT INTO property_media (property_id, media_type, media_path, sort_order, created_at) VALUES (?, ?, ?, ?, NOW())"
        );

        $order = 0;
        foreach ($media as $m) {
            $order++;
            $type = $m['type'] ?? 'image';
            $path = $m['path'] ?? '';
            $stmt->execute([$propertyId, $type, $path, $order]);
        }
        return true;
    }

    // Fetch media rows for a single property
    public static function getMedia(int $propertyId)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT media_type, media_path FROM property_media WHERE property_id = ? ORDER BY sort_order ASC");
        $stmt->execute([$propertyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getFirstImagePath(int $propertyId)
    {
        $media = self::getMedia($propertyId);
        return !empty($media) ? ($media[0]['media_path'] ?? null) : null;
    }

    // Get properties by loai_kho with optional search, pagination
    public static function getByLoaiKho(string $loai_kho, int $limit = 20, int $offset = 0, ?string $search = null, ?string $trang_thai = null)
    {
        $db = self::db();
        $limit = (int)$limit;
        $offset = (int)$offset;

        $params = [];
        $params[] = $loai_kho;

        $sql = "SELECT * FROM properties WHERE loai_kho = ?";

        if ($trang_thai) {
            $sql .= " AND trang_thai = ?";
            $params[] = $trang_thai;
        }

        if ($search) {
            $like = '%' . $search . '%';
            $sql .= " AND (tieu_de LIKE ? OR ma_hien_thi LIKE ? OR dia_chi_chi_tiet LIKE ?)
                      ORDER BY id DESC LIMIT $limit OFFSET $offset";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        } else {
            $sql .= " ORDER BY id DESC LIMIT $limit OFFSET $offset";
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    public static function countByLoaiKho(string $loai_kho, ?string $search = null, ?string $trang_thai = null)
    {
        $db = self::db();
        $params = [];
        $params[] = $loai_kho;

        $sql = "SELECT COUNT(*) FROM properties WHERE loai_kho = ?";
        if ($trang_thai) {
            $sql .= " AND trang_thai = ?";
            $params[] = $trang_thai;
        }

        if ($search) {
            $like = '%' . $search . '%';
            $sql .= " AND (tieu_de LIKE ? OR ma_hien_thi LIKE ? OR dia_chi_chi_tiet LIKE ?)";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    // Find a single property by its visible code `ma_hien_thi`
    public static function findByMaHienThi(string $ma)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT * FROM properties WHERE ma_hien_thi = ? LIMIT 1");
        $stmt->execute([$ma]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Find a single property by its primary id
    public static function findById(int $id)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT * FROM properties WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Update the status (trang_thai) of a property by id.
    public static function updateStatus(int $id, string $trang_thai)
    {
        $allowed = [
            'ban_manh',
            'tam_dung_ban',
            'dung_ban',
            'da_ban',
            'tang_chao',
            'ha_chao'
        ];
        if (!in_array($trang_thai, $allowed, true)) {
            return false;
        }

        $db = self::db();
        $stmt = $db->prepare("UPDATE properties SET trang_thai = ?, updated_at = NOW() WHERE id = ?");
        try {
            return (bool)$stmt->execute([$trang_thai, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
