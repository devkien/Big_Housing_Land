<?php

class User extends Model
{
    public static function findForLogin($identity)
    {
        $db = self::db();
        $stmt = $db->prepare("
            SELECT * FROM users
            WHERE email = ?
               OR so_dien_thoai = ?
               OR so_cccd = ?
            LIMIT 1
        ");
        $stmt->execute([$identity, $identity, $identity]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findByEmail($email)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findById($id)
    {
        $db = self::db();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        $db = self::db();
            // The original implementation contained a SQL typo and mismatched
            // placeholders which could cause runtime DB errors. Delegate to the
            // proven `createWithRole` implementation to keep a single source of
            // truth for user creation logic.
            return self::createWithRole($data);
    }

    // ===== CHECK PHONE =====
    public static function findByPhone($phone)
    {
        $db = self::db();
        $stmt = $db->prepare(
            "SELECT id FROM users WHERE so_dien_thoai = ? LIMIT 1"
        );
        $stmt->execute([$phone]);
        return $stmt->fetch();
    }

    // ===== UPDATE PROFILE =====
    public static function update($id, $data)
    {
        $db = self::db();
        $stmt = $db->prepare(
            "UPDATE users SET ho_ten = ?, so_dien_thoai = ?, email = ?, nam_sinh = ?, so_cccd = ?, dia_chi = ?, updated_at = NOW() WHERE id = ?"
        );

        return $stmt->execute([
            $data['ho_ten'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['email'] ?? null,
            $data['nam_sinh'] ?? null,
            $data['so_cccd'] ?? null,
            $data['dia_chi'] ?? null,
            $id
        ]);
    }


    // ===== RESET PASSWORD =====

    public static function createResetToken($email, $token)
    {
        $db = self::db();
        $stmt = $db->prepare(
            "INSERT INTO password_resets (email, token) VALUES (?, ?)"
        );
        $stmt->execute([$email, $token]);
    }

    public static function getEmailByToken($token)
    {
        $db = self::db();
        $stmt = $db->prepare(
            "SELECT email FROM password_resets WHERE token = ? LIMIT 1"
        );
        $stmt->execute([$token]);
        return $stmt->fetchColumn();
    }

    public static function updatePassword($email, $password)
    {
        $db = self::db();
        $stmt = $db->prepare(
            "UPDATE users SET password = ?, token = NULL WHERE email = ?"
        );
        $stmt->execute([$password, $email]);
    }

    // Update password by user id (used for change-password by authenticated user)
    public static function updatePasswordById($id, $password)
    {
        $db = self::db();
        $stmt = $db->prepare(
            "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$password, $id]);
    }

    public static function deleteToken($token)
    {
        $db = self::db();
        $stmt = $db->prepare(
            "DELETE FROM password_resets WHERE token = ?"
        );
        $stmt->execute([$token]);
    }

    // ===== ROLE / LISTING HELPERS =====
    // Get users by role (quyen) with optional pagination and search
    public static function getByRole($role, $limit = 10, $offset = 0, $search = null)
    {
        $db = self::db();
        // Some MySQL/PDO drivers don't allow binding LIMIT/OFFSET as parameters.
        // Interpolate the integer values directly into the SQL to avoid syntax errors.
        $limit = (int) $limit;
        $offset = (int) $offset;
        if ($search) {
            $sql = "SELECT * FROM users WHERE quyen = ? AND (ho_ten LIKE ? OR so_dien_thoai LIKE ? OR ma_nhan_su LIKE ?) ORDER BY id DESC LIMIT $limit OFFSET $offset";
            $stmt = $db->prepare($sql);
            $like = '%' . $search . '%';
            $stmt->execute([$role, $like, $like, $like]);
        } else {
            $sql = "SELECT * FROM users WHERE quyen = ? ORDER BY id DESC LIMIT $limit OFFSET $offset";
            $stmt = $db->prepare($sql);
            $stmt->execute([$role]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function countByRole($role, $search = null)
    {
        $db = self::db();
        if ($search) {
            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM users WHERE quyen = ? AND (ho_ten LIKE ? OR so_dien_thoai LIKE ? OR ma_nhan_su LIKE ?)"
            );
            $like = '%' . $search . '%';
            $stmt->execute([$role, $like, $like, $like]);
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE quyen = ?");
            $stmt->execute([$role]);
        }
        return (int) $stmt->fetchColumn();
    }

    // ===== DELETE USER =====
    public static function deleteById($id)
    {
        $db = self::db();
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Create user and set both quyen (role) and loai_tai_khoan
    public static function createWithRole($data)
    {
        $db = self::db();
        $stmt = $db->prepare(
            "INSERT INTO users (
                ma_nhan_su,
                so_dien_thoai,
                password,
                ho_ten,
                nam_sinh,
                email,
                gioi_tinh,
                loai_tai_khoan,
                quyen,
                phong_ban,
                so_cccd,
                dia_chi,
                link_fb,
                ma_gioi_thieu,
                anh_cccd,
                trang_thai,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );

        $loai = $data['loai_tai_khoan'] ?? ((($data['quyen'] ?? '') === 'admin') ? 'admin' : 'nhan_vien');

        return $stmt->execute([
            $data['ma_nhan_su'] ?? null,
            $data['so_dien_thoai'] ?? null,
            $data['password'] ?? null,
            $data['ho_ten'] ?? null,
            $data['nam_sinh'] ?? null,
            $data['email'] ?? null,
            $data['gioi_tinh'] ?? null,
            $loai,
            $data['quyen'] ?? 'user',
            $data['phong_ban'] ?? null,
            $data['so_cccd'] ?? null,
            $data['dia_chi'] ?? null,
            $data['link_fb'] ?? null,
            $data['ma_gioi_thieu'] ?? null,
            $data['anh_cccd'] ?? null,
            $data['trang_thai'] ?? 1,
        ]);
    }
}
