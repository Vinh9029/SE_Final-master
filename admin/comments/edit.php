<?php
session_start();
include_once '../../../database/db_connection.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // For AJAX request, send JSON error
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Authentication required.']);
        exit();
    }
    // For regular request, redirect
    header("Location: ../../../login/index.php");
    exit();
}

$comment_id = null;
$content = '';
$error = '';
$message = '';

// Handle POST request for updating the comment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = $_POST['comment_id'] ?? null;
    $content = $_POST['content'] ?? '';

    if ($comment_id && !empty($content)) {
        $stmt = $db_connection->prepare("UPDATE comments SET content = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $content, $comment_id);
        
        if ($stmt->execute()) {
            // For AJAX request from dashboard
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Comment updated successfully!', 'redirect' => 'comments/list.php']);
                exit();
            }
            // For regular form submission
            $_SESSION['message'] = "Comment updated successfully!";
            header("Location: list.php");
            exit();
        } else {
            $error = "Failed to update comment.";
        }
        $stmt->close();
    } else {
        $error = "Content cannot be empty.";
    }

    // Handle POST error for AJAX
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error]);
        exit();
    }
}

// Handle GET request for displaying the form
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $comment_id = $_GET['id'] ?? null;
    if ($comment_id && is_numeric($comment_id)) {
        $stmt = $db_connection->prepare("SELECT content FROM comments WHERE id = ?");
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $comment = $result->fetch_assoc();
            $content = $comment['content'];
        } else {
            $error = "Comment not found.";
        }
        $stmt->close();
    } else {
        $error = "No valid comment ID provided.";
    }
}

$db_connection->close();

// The HTML part below will be rendered for GET requests, 
// or if a non-AJAX POST request fails.
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Comment</h1>
        <a href="#" data-page="comments/list.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
            &larr; Back to List
        </a>
    </div>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <?php if (!$error && $comment_id): ?>
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <form action="comments/edit.php" method="POST">
            <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment_id); ?>">
            
            <div class="mb-6">
                <label for="content" class="block text-gray-700 text-sm font-bold mb-2">Comment Content:</label>
                <textarea id="content" name="content" rows="8" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-pink-500 transition duration-300"><?php echo htmlspecialchars($content); ?></textarea>
            </div>
            
            <div class="flex items-center justify-end">
                <button type="submit" class="bg-pink-500 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-300">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>