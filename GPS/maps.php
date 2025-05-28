<?php 
include 'auth.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Maps</title>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center min-h-screen p-4">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-2xl text-center">
        <h1 class="text-2xl font-bold mb-4">Maps</h1>
        <iframe 
            src="https://www.google.com/maps/embed?..." 
            width="100%" 
            height="450" 
            class="rounded-lg shadow-md border border-gray-300"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>
</body>
</html>
