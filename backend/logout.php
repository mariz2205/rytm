<?php
session_start();
header('Content-Type: application/json');

// clear session
session_unset();
session_destroy();

echo json_encode([
    "success" => true,
    "redirect" => "../frontend/index.html" // redirect after logout
]);
exit;
