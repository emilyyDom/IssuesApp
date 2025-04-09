<?php
session_start();
if (!isset($_SESSION['user_id'])){
    session_destroy();
    header("Location: login.php");
}
require 'database.php'; // Database connection

$pdo = Database::connect();
$error_message = "";

// Fetch persons for dropdown list
$persons_sql = "SELECT id, fname, lname FROM iss_persons ORDER BY lname ASC";
$persons_stmt = $pdo->query($persons_sql);
$persons = $persons_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch persons for dropdown list
$issues_sql = "SELECT * FROM iss_issues ORDER BY id ASC";
$issues_stmt = $pdo->query($issues_sql);
$issues = $issues_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Create, Update, Delete actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle adding a new comment
    if (isset($_POST['add_comment'])) {
        // No need for 'id' here since it's auto-incremented in the database
        $per_id = $_POST['per_id'];
        $iss_id = $_POST['iss_id'];
        $short_comment = trim($_POST['short_comment']);
        $long_comment = trim($_POST['long_comment']);
        $posted_date = $_POST['posted_date'];
    
        // Corrected SQL query (removed the trailing comma)
        $sql = "INSERT INTO iss_comments (per_id, iss_id, short_comment, long_comment, posted_date) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$per_id, $iss_id, $short_comment, $long_comment, $posted_date]);
    
        header("Location: comments_list.php");
        exit();
    }

    // Handle updating an existing comment
    if (isset($_POST['update_comment'])) {
        $id = $_POST['id'];
        $short_comment = trim($_POST['short_comment']);
        $long_comment = trim($_POST['long_comment']);
        $posted_date = $_POST['posted_date'];

        $sql = "UPDATE iss_comments SET short_comment=?, long_comment=?, posted_date=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$short_comment, $long_comment, $posted_date, $id]);

        header("Location: comments_list.php");
        exit();
    }

    // Handle deleting a comment
    if (isset($_POST['delete_comment'])) {
        $id = $_POST['id'];

        $sql = "DELETE FROM iss_comments WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        header("Location: comments_list.php");
        exit();
    }
}

$iss_id = $_GET['issue_id'];


// Fetch all comments
$sql = "SELECT * FROM iss_comments WHERE iss_id = $iss_id ORDER BY posted_date DESC";
$comments = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments List - DSR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-3">
        <h2 class="text-center">Comments List</h2>

        <!-- Navigation Buttons -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <h3>All Comments</h3>
            <div>
                <a href="issues_list.php" class="btn btn-primary">Back to Issues</a>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCommentModal">+</button>
            </div>
        </div>

        <!-- Add Comment Modal -->
        <div class="modal fade" id="addCommentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Comment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST">

                            <select name="per_id" class="form-control mb-2">
                                <option value="">Select Person</option>
                                <?php foreach ($persons as $person) : ?>
                                    <option value="<?= $person['id']; ?>"><?= htmlspecialchars($person['fname'] . " " . $person['lname']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="iss_id" class="form-control mb-2">
                                <option value="">Select Issue</option>
                                <?php foreach ($issues as $issue) : ?>
                                    <option value="<?= $issue['id']; ?>"><?= htmlspecialchars($issue['short_description']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="short_comment" class="form-control mb-2" placeholder="Short Comment" required>
                            <textarea name="long_comment" class="form-control mb-2" placeholder="Long Comment"></textarea>
                            <input type="date" name="posted_date" class="form-control mb-2" required>
                            
                            <button type="submit" name="add_comment" class="btn btn-primary">Add Issue</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <table class="table table-striped table-sm mt-2">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Person ID</th>
                    <th>Issue ID</th>
                    <th>Short Comment</th>
                    <th>Posted Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment) : ?>
                    <tr>
                        <td><?= htmlspecialchars($comment['id']); ?></td>
                        <td><?= htmlspecialchars($comment['per_id']); ?></td>
                        <td><?= htmlspecialchars($comment['iss_id']); ?></td>
                        <td><?= htmlspecialchars($comment['short_comment']); ?></td>
                        <td><?= htmlspecialchars($comment['posted_date']); ?></td>
                        <td>
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#readComment<?= $comment['id']; ?>">R</button>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateComment<?= $comment['id']; ?>">U</button>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteComment<?= $comment['id']; ?>">D</button>
                        </td>
                    </tr>

                    <!-- Read Modal -->
                    <div class="modal fade" id="readComment<?= $comment['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Comment Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Short Comment:</strong> <?= htmlspecialchars($comment['short_comment']); ?></p>
                                    <p><strong>Long Comment:</strong> <?= htmlspecialchars($comment['long_comment']); ?></p>
                                    <p><strong>Posted Date:</strong> <?= htmlspecialchars($comment['posted_date']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Modal -->
                    <div class="modal fade" id="updateComment<?= $comment['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Comment</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $comment['id']; ?>">
                                        <input type="text" name="short_comment" class="form-control mb-2" value="<?= htmlspecialchars($comment['short_comment']); ?>" required>
                                        <textarea name="long_comment" class="form-control mb-2"><?= htmlspecialchars($comment['long_comment']); ?></textarea>
                                        <input type="date" name="posted_date" class="form-control mb-2" value="<?= $comment['posted_date']; ?>" required>
                                        <button type="submit" name="update_comment" class="btn btn-primary">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteComment<?= $comment['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Confirm Deletion</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete this comment?</p>
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $comment['id']; ?>">
                                        <button type="submit" name="delete_comment" class="btn btn-danger">Delete</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php Database::disconnect(); ?>
