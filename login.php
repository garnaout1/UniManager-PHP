<?php
session_start(); require_once 'includes/db.php';
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("SELECT id, username, password, role_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $_POST['email']); $stmt->execute();
    $res = $stmt->get_result();
    if ($user = $res->fetch_assoc()) {
        if (password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user_id'] = $user['id']; $_SESSION['username'] = $user['username']; $_SESSION['role_id'] = $user['role_id'];
            header("location: dashboard.php"); exit;
        } else { $error = "Invalid password."; }
    } else { $error = "User not found."; }
}
?>
<!DOCTYPE html>
<html><head><title>Login</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<div class="form-container">
    <h2>Login</h2>
    <p style="color:red"><?php echo $error; ?></p>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <button type="submit">Login</button>
    </form>
</div>
</body></html>
