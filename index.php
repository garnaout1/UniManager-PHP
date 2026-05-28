<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Campus - Home</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
    <header>
        <nav>
            <div class="logo">University System</div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Sign Up</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section id="hero">
            <h1>Welcome to Our Campus</h1>
            <p>Providing excellence in education since 2025.</p>
        </section>
        <section id="map-section">
            <h2>Find Us</h2>
            <div id="map"></div>
        </section>
    </main>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
