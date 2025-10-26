<?php
// Internal handler for creating new notifications

/**
 * Creates a new notification for a user.
 *
 * @param mysqli $conn The database connection object.
 * @param int $customer_id The ID of the customer to notify.
 * @param string $type The type of notification (e.g., 'order_status', 'welcome').
 * @param string $title The title of the notification.
 * @param string $body The main content of the notification.
 * @param int|null $related_id An ID related to the notification (e.g., order_id).
 * @param string|null $url A URL to link to when the notification is clicked.
 * @return bool True on success, false on failure.
 */
function create_notification($conn, $customer_id, $type, $title, $body, $related_id = null, $url = null) {
    $sql = "INSERT INTO notifications (customer_id, type, title, body, related_id, url) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        // Handle prepare error
        return false;
    }
    
    $stmt->bind_param('isssis', $customer_id, $type, $title, $body, $related_id, $url);
    
    $success = $stmt->execute();
    
    $stmt->close();
    
    return $success;
}

// Example of how this might be used from another file:
/*
include_once __DIR__ . '/path/to/createNotification.php';
include_once __DIR__ . '/path/to/db_connection.php';

// When a user registers:
$user_id = 123; // The new user's ID
create_notification(
    $conn,
    $user_id,
    'welcome',
    'Welcome to Old Flavour!',
    'Thanks for joining us. We hope you enjoy our coffee.',
    null,
    '/customer/account.php'
);

// When an order status changes:
$user_id = 123;
$order_id = 456;
create_notification(
    $conn,
    $user_id,
    'order_status',
    'Order #456 Update',
    'Your order is now being processed.',
    $order_id,
    '/customer/orders.php?id=456'
);
*/
?>