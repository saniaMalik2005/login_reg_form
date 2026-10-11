<?php
include 'db.php';
session_start();
$message = "";

// অটো-লগইন চেক (Remember Me Cookie থাকলে)
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_email'])) {
    $_SESSION['email'] = $_COOKIE['remember_email'];
    // আপনি চাইলে কুকি থেকে আইডি ফেচ করেও সেশন সেট করতে পারেন
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, password, failed_attempts, lockout_time FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashedPassword, $failed_attempts, $lockout_time);
            $stmt->fetch();

            // অ্যাকাউন্ট লকআউট চেক (৩০ সেকেন্ডের জন্য লক থাকবে যদি ৩ বার ভুল হয়)
            $current_time = time();
            if ($failed_attempts >= 3 && strtotime($lockout_time) > $current_time) {
                $wait_time = ceil((strtotime($lockout_time) - $current_time));
                $message = "অ্যাকাউন্ট সাময়িকভাবে লক করা হয়েছে। {$wait_time} সেকেন্ড পর চেষ্টা করুন।";
            } else {
                if (password_verify($password, $hashedPassword)) {
                    // সফল লগইন হলে ফেল্ড কাউন্ট রিসেট করা
                    $resetStmt = $conn->prepare("UPDATE users SET failed_attempts = 0, lockout_time = NULL WHERE id = ?");
                    $resetStmt->bind_param("i", $id);
                    $resetStmt->execute();
                    $resetStmt->close();

                    $_SESSION['user_id'] = $id;
                    $_SESSION['email'] = $email;

                    // Remember Me কুকি সেট করা (৩০ দিনের জন্য)
                    if ($remember) {
                        setcookie('remember_email', $email, time() + (86400 * 30), "/");
                    }

                    header("Location: dashboard.php");
                    exit();
                } else {
                    // ভুল পাসওয়ার্ড হলে অ্যাটম্প্ট বাড়ানো
                    $failed_attempts++;
                    if ($failed_attempts >= 3) {
                        $lock_until = date("Y-m-d H:i:s", time() + 30); // ৩০ সেকেন্ড লক
                        $lockStmt = $conn->prepare("UPDATE users SET failed_attempts = ?, lockout_time = ? WHERE id = ?");
                        $lockStmt->bind_param("isi", $failed_attempts, $lock_until, $id);
                        $lockStmt->execute();
                        $lockStmt->close();
                        $message = "পরপর ৩ বার ভুল পাসওয়ার্ড! অ্যাকাউন্ট ৩০ সেকেন্ডের জন্য লক করা হলো।";
                    } else {
                        $failStmt = $conn->prepare("UPDATE users SET failed_attempts = ? WHERE id = ?");
                        $failStmt->bind_param("ii", $failed_attempts, $id);
                        $failStmt->execute();
                        $failStmt->close();
                        $remaining = 3 - $failed_attempts;
                        $message = "ভুল পাসওয়ার্ড! আর {$remaining} বার ভুল করলে অ্যাকাউন্ট লক হবে।";
                    }
                }
            }
        } else {
            $message = "এই ইমেইল দিয়ে কোনো অ্যাকাউন্ট নেই!";
        }
        $stmt->close();
    } else {
        $message = "সব ফিল্ড পূরণ করুন!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advanced Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow p-4 w-100" style="max-width: 400px;">
        <h3 class="text-center mb-3">Welcome Back</h3>
        <?php if (!empty($message)): ?>
            <div class="alert alert-danger py-2"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Remember Me</label>
            </div>
            <button type="submit" class="btn btn-success w-100">Login</button>
        </form>
        <p class="text-center mt-3 mb-0">Don't have an account? <a href="register.php">Register</a></p>
    </div>
</body>
</html>