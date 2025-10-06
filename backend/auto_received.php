<?php
include "db.php";

// Auto mark orders as "Received" if 2 days have passed after delivery
$query = "
    UPDATE orderlist
    SET OrderStatus = 'Received'
    WHERE OrderStatus = 'Delivered'
    AND DeliveryDate IS NOT NULL
    AND DeliveryDate <= DATE_SUB(NOW(), INTERVAL 2 DAY)
";

$conn->query($query);
?>
