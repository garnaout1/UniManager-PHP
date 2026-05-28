<?php
require_once 'includes/db.php';
$error = ""; $success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username']; $email = $_POST['email']; $password = $_POST['password'];
    $role_id = $_POST['role_id']; $code = $_POST['registration_code'];
    $is_valid = ($role_id == "1" && $code == "STUD2025") || ($role_id == "2" && $code == "PROF2025");
    if (!$is_valid) { $error = "Incorrect registration code."; }
    else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $username, $email, $hashed, $role_id);
        if ($stmt->execute()) { $success = "Success! <a href='login.php'>Login</a>"; }
        else { $error = "Email already exists."; }
    }
}
?>
<!DOCTYPE html>
<html><head><title>Sign Up</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<div class="form-container">
    <h2>Register</h2>
    <p style="color:red"><?php echo $error; ?></p><p style="color:green"><?php echo $success; ?></p>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <select name="role_id"><option value="1">Student</option><option value="2">Teacher</option></select><br><br>
        <input type="text" name="registration_code" placeholder="Code" required><br><br>
        <button type="submit">Sign Up</button>
    </form>
</div>
</body></html>
