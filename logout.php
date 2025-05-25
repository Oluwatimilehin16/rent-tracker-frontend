<?php
session_start();

// Only proceed if session is active
if (isset($_SESSION['tenant_id']) || isset($_SESSION['landlord_id'])) {
    session_unset();     // Unset all session variables
    session_destroy();   // Destroy the session
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging Out...</title>
    <!-- SweetAlert2 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.5.5/dist/sweetalert2.min.css">
    <!-- SweetAlert2 JS (Ensure this is properly linked) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        // Show SweetAlert2 modal with custom colors
        Swal.fire({
            title: 'Logged Out',
            text: 'You’ve been logged out successfully.',
            icon: 'success',
            confirmButtonText: 'OK',
            background: '#ffffff',  // White background
            confirmButtonColor: '#003c00',  // Dark Green button
            iconColor: '#003c00',  // Dark Green icon
            textColor: '#555',  // Default text color (you can change to #003c00 for dark green)
            willClose: () => {
                window.location.href = "login.php";
            }
        });
    </script>
</body>
</html>
