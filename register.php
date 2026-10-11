<?php
include 'db.php';
$message = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        if (strlen($password) < 6) {
            $message = "পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে!";
            $msg_type = "danger";
        } else {
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $message = "এই ইমেইল দিয়ে আগেই অ্যাকাউন্ট খোলা হয়েছে!";
                $msg_type = "warning";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $email, $hashedPassword);

                if ($stmt->execute()) {
                    $message = "রেজিস্ট্রেশন সফল হয়েছে! এখন <a href='login.php' class='alert-link'>লগইন</a> করুন।";
                    $msg_type = "success";
                } else {
                    $message = "কোথাও সমস্যা হয়েছে, আবার চেষ্টা করুন।";
                    $msg_type = "danger";
                }
                $stmt->close();
            }
            $checkStmt->close();
        }
    } else {
        $message = "সব ফিল্ড পূরণ করুন!";
        $msg_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advanced Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow p-4 w-100" style="max-width: 400px;">
        <h3 class="text-center mb-3">Create Account</h3>
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $msg_type; ?> py-2"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password (Min 6 chars)</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
        <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>