<?php
// Fetch theme from the database
$theme_class = 'theme-light'; // Default theme

$stmt = $conn->prepare("SELECT theme FROM user_settings WHERE username=?");
$stmt->bind_param("s", $_SESSION['user']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $theme = $row['theme'];
    if ($theme === 'Dark') {
        $theme_class = 'theme-dark';
    } elseif ($theme === 'Auto') {
        $theme_class = (date('H') >= 18 || date('H') < 6) ? 'theme-dark' : 'theme-light';
    }
}
$stmt->close();
?>