<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Website</title>
</head>
<body>

    <h1>Welcome to my website!</h1>

    <?php
    // Simulate login status for demonstration
    $isLoggedIn = true; // Change to false to hide the scripts
    ?>

    <!-- Your website content here -->

    <?php if ($isLoggedIn): ?>
        <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
        <script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>
        <script src="auth/firebase-config.js"></script>
        <script src="auth/auth.js"></script>
    <?php endif; ?>

</body>
</html>
