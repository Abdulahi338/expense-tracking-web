<?php
namespace App\Auth;

use App\Core\Security;
use PDO;

class User
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Create a new user
     */
    public function register($username, $email, $password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $otp = sprintf("%06d", mt_rand(1, 999999));
        $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $sql = "INSERT INTO users (username, email, password_hash, otp_code, otp_expiry) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);

        try {
            $stmt->execute([$username, $email, $hash, $otp, $expiry]);
            return ['status' => true, 'otp' => $otp];
        }
        catch (\PDOException $e) {
            return ['status' => false, 'message' => 'Registration failed: Duplicate email or username.'];
        }
    }

    /**
     * Authenticate user
     */
    public function login($email, $password)
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if (!$user['is_verified']) {
                return ['status' => false, 'message' => 'Please verify your email first.', 'unverified' => true];
            }
            return ['status' => true, 'user' => $user];
        }

        return ['status' => false, 'message' => 'Invalid email or password.'];
    }

    /**
     * Verify OTP
     */
    public function verifyOTP($email, $otp)
    {
        $sql = "SELECT id, otp_code, otp_expiry FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user)
            return false;

        if ($user['otp_code'] === $otp && strtotime($user['otp_expiry']) > time()) {
            $sql = "UPDATE users SET is_verified = 1, otp_code = NULL, otp_expiry = NULL WHERE id = ?";
            $this->db->prepare($sql)->execute([$user['id']]);
            return true;
        }

        return false;
    }

    /**
     * Generate Password Reset Token
     */
    public function generateResetToken($email)
    {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql = "UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token, $expiry, $email]);

        return ($stmt->rowCount() > 0) ? $token : false;
    }
}
