<?php
session_start();
require_once 'includes/db.php';

// RBAC: Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role_id = $_SESSION['role_id']; // 1 = Student, 2 = Teacher

// Handle Post Actions (Teacher creating course/assignment, Student submitting)
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // TEACHER ACTIONS
    if ($role_id == 2) {
        if (isset($_POST['create_course'])) {
            $name = $_POST['course_name'];
            $stmt = $conn->prepare("INSERT INTO courses (course_name, teacher_id) VALUES (?, ?)");
            $stmt->bind_param("si", $name, $user_id);
            $stmt->execute();
            $message = "Course created successfully!";
        }
        if (isset($_POST['post_assignment'])) {
            $c_id = $_POST['course_id'];
            $title = $_POST['title'];
            $desc = $_POST['description'];
            $due = $_POST['due_date'];
            $stmt = $conn->prepare("INSERT INTO assignments (course_id, title, description, due_date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $c_id, $title, $desc, $due);
            $stmt->execute();
            $message = "Assignment posted!";
        }
        if (isset($_POST['grade_submission'])) {
            $sub_id = $_POST['submission_id'];
            $grade = $_POST['grade'];
            $stmt = $conn->prepare("UPDATE submissions SET grade = ? WHERE id = ?");
            $stmt->bind_param("si", $grade, $sub_id);
            $stmt->execute();
            $message = "Grade updated!";
        }
    }
    
    // STUDENT ACTIONS
    if ($role_id == 1) {
        if (isset($_POST['submit_work'])) {
            $as_id = $_POST['assignment_id'];
            $text = $_POST['submission_text'];
            $stmt = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, submission_text) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $as_id, $user_id, $text);
            $stmt->execute();
            $message = "Work submitted successfully!";
        }
        if (isset($_POST['enroll'])) {
            $c_id = $_POST['course_id'];
            $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $c_id);
            @$stmt->execute(); // Ignore if already enrolled
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>University Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard-container { display: flex; }
        .sidebar { width: 250px; background: #2c3e50; color: white; min-height: 100vh; padding: 20px; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar ul li { padding: 10px 0; border-bottom: 1px solid #34495e; }
        .sidebar a { color: white; text-decoration: none; }
        .content { flex: 1; padding: 20px; }
        .card { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .forbidden { color: red; font-weight: bold; background: #fee; padding: 20px; border: 2px solid red; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Common Sidebar with Role-Based Menus -->
        <div class="sidebar">
            <h3>Menu</h3>
            <p>Welcome, <?php echo htmlspecialchars($username); ?></p>
            <p><small>(Role: <?php echo $role_id == 2 ? "Teacher" : "Student"; ?>)</small></p>
            <hr>
            <ul>
                <li><a href="dashboard.php">Overview</a></li>
                <?php if ($role_id == 2): ?>
                    <!-- Teacher Only Menu -->
                    <li><a href="?view=my_courses">My Courses</a></li>
                    <li><a href="?view=submissions">Submissions</a></li>
                <?php else: ?>
                    <!-- Student Only Menu -->
                    <li><a href="?view=available_courses">All Courses</a></li>
                    <li><a href="?view=my_enrolled">My Enrolled Courses</a></li>
                    <li><a href="?view=my_grades">My Grades</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="content">
            <?php if ($message) echo "<p style='color:green'>$message</p>"; ?>

            <?php
            $view = isset($_GET['view']) ? $_GET['view'] : 'overview';

            // FORBIDDEN ACTION CHECK
            $teacher_views = ['my_courses', 'submissions'];
            $student_views = ['available_courses', 'my_enrolled', 'my_grades'];

            if ($role_id == 1 && in_array($view, $teacher_views)) {
                echo "<div class='forbidden'>Forbidden Action: Students cannot access teacher features.</div>";
            } elseif ($role_id == 2 && in_array($view, $student_views)) {
                echo "<div class='forbidden'>Forbidden Action: Teachers cannot access student features.</div>";
            } else {
                // CONTENT LOGIC
                if ($view == 'overview') {
                    echo "<h2>Dashboard Overview</h2><p>Select an option from the sidebar to manage your activities.</p>";
                }

                // TEACHER VIEWS
                if ($role_id == 2) {
                    if ($view == 'my_courses') {
                        echo "<h2>Manage Courses</h2>";
                        ?>
                        <form method="POST" class="card">
                            <h3>Create New Course</h3>
                            <input type="text" name="course_name" placeholder="Course Name" required>
                            <button type="submit" name="create_course">Create</button>
                        </form>
                        <div class="card">
                            <h3>My Courses & Post Assignments</h3>
                            <?php
                            $courses = $conn->query("SELECT * FROM courses WHERE teacher_id = $user_id");
                            while ($c = $courses->fetch_assoc()) {
                                echo "<div><strong>" . htmlspecialchars($c['course_name']) . "</strong>";
                                ?>
                                <form method="POST" style="margin-top:10px; border-top:1px solid #eee; padding-top:10px;">
                                    <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
                                    <input type="text" name="title" placeholder="Assignment Title" required><br>
                                    <textarea name="description" placeholder="Description"></textarea><br>
                                    <input type="date" name="due_date" required><br>
                                    <button type="submit" name="post_assignment" style="width:auto">Post Assignment</button>
                                </form>
                                <?php
                                echo "</div><hr>";
                            }
                            ?>
                        </div>
                        <?php
                    }
                    if ($view == 'submissions') {
                        echo "<h2>Student Submissions</h2>";
                        $subs = $conn->query("SELECT s.*, a.title as a_title, u.username as s_name 
                                            FROM submissions s 
                                            JOIN assignments a ON s.assignment_id = a.id 
                                            JOIN courses c ON a.course_id = c.id 
                                            JOIN users u ON s.student_id = u.id
                                            WHERE c.teacher_id = $user_id");
                        while ($s = $subs->fetch_assoc()) {
                            echo "<div class='card'>";
                            echo "<p><strong>Assignment:</strong> {$s['a_title']} | <strong>Student:</strong> {$s['s_name']}</p>";
                            echo "<p><strong>Text:</strong> " . nl2br(htmlspecialchars($s['submission_text'])) . "</p>";
                            echo "<form method='POST'>
                                    <input type='hidden' name='submission_id' value='{$s['id']}'>
                                    <input type='text' name='grade' value='{$s['grade']}' size='5'>
                                    <button type='submit' name='grade_submission' style='width:auto'>Update Grade</button>
                                  </form>";
                            echo "</div>";
                        }
                    }
                }

                // STUDENT VIEWS
                if ($role_id == 1) {
                    if ($view == 'available_courses') {
                        echo "<h2>Available Courses</h2>";
                        $courses = $conn->query("SELECT c.*, u.username as t_name FROM courses c JOIN users u ON c.teacher_id = u.id");
                        while ($c = $courses->fetch_assoc()) {
                            echo "<div class='card'>
                                    <strong>" . htmlspecialchars($c['course_name']) . "</strong> (Teacher: {$c['t_name']})
                                    <form method='POST' style='display:inline; margin-left:10px;'>
                                        <input type='hidden' name='course_id' value='{$c['id']}'>
                                        <button type='submit' name='enroll' style='width:auto'>Enroll</button>
                                    </form>
                                  </div>";
                        }
                    }
                    if ($view == 'my_enrolled') {
                        echo "<h2>My Courses & Assignments</h2>";
                        $courses = $conn->query("SELECT c.* FROM courses c JOIN enrollments e ON c.id = e.course_id WHERE e.student_id = $user_id");
                        while ($c = $courses->fetch_assoc()) {
                            echo "<div class='card'><h3>" . htmlspecialchars($c['course_name']) . "</h3>";
                            $assigs = $conn->query("SELECT * FROM assignments WHERE course_id = " . $c['id']);
                            while ($a = $assigs->fetch_assoc()) {
                                echo "<div style='margin-left:20px; border-left:2px solid #ddd; padding-left:10px;'>";
                                echo "<strong>" . htmlspecialchars($a['title']) . "</strong> (Due: {$a['due_date']})<br>";
                                echo "<small>" . htmlspecialchars($a['description']) . "</small>";
                                ?>
                                <form method="POST" style="margin-top:5px;">
                                    <input type="hidden" name="assignment_id" value="<?php echo $a['id']; ?>">
                                    <textarea name="submission_text" placeholder="Type your work here..." required></textarea><br>
                                    <button type="submit" name="submit_work" style="width:auto">Submit Assignment</button>
                                </form>
                                <?php
                                echo "</div><br>";
                            }
                            echo "</div>";
                        }
                    }
                    if ($view == 'my_grades') {
                        echo "<h2>My Grades</h2>";
                        $grades = $conn->query("SELECT s.*, a.title as a_title FROM submissions s 
                                               JOIN assignments a ON s.assignment_id = a.id 
                                               WHERE s.student_id = $user_id");
                        echo "<table border='1' width='100%' cellpadding='10' style='border-collapse:collapse;'>
                                <tr><th>Assignment</th><th>Submitted At</th><th>Grade</th></tr>";
                        while ($g = $grades->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$g['a_title']}</td>
                                    <td>{$g['submitted_at']}</td>
                                    <td style='color:blue; font-weight:bold;'>{$g['grade']}</td>
                                  </tr>";
                        }
                        echo "</table>";
                    }
                }
            }
            ?>
        </div>
    </div>
</body>
</html>
