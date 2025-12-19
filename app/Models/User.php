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
        $stmt = $db->prepare("
            INSERT INTO users (
                ma_nhan_su,
                so_dien_thoai,
                password,
                ho_ten,
                nam_sinh,
                email,
                gioi_tinh,
                loai_tai_khoan
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['ma_nhan_su'] ?? null,
            $data['so_dien_thoai'],
            $data['password'],
            $data['ho_ten'],
            $data['nam_sinh'],
            $data['email'] ?? null,
            $data['gioi_tinh'] ?? 'Khác',
            $data['loai_tai_khoan'] ?? 'nhan_vien'
        ]);
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

    public static function deleteToken($token)
    {
        $db = self::db();
        $stmt = $db->prepare(
            "DELETE FROM password_resets WHERE token = ?"
        );
        $stmt->execute([$token]);
    }
}
