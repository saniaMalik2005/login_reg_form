<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Secure App</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </nav>
    <div class="container mt-5">
        <div class="card shadow p-4 text-center">
            <h2>স্বাগতম, <span class="text-primary"><?php echo htmlspecialchars($_SESSION['email']); ?></span>!</h2>
            <p class="text-muted mt-2">আপনি সফলভাবে সিকিওর ড্যাশবোর্ডে প্রবেশ করেছেন।</p>
        </div>
    </div>
</body>
</html>