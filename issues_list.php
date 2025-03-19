<?php
session_start();
include 'database.php'; // Include database connection file
$pdo = Database::connect(); // Get PDO connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch issues from the database
$sql = "SELECT id, short_description, long_description, open_date, close_date, priority, org, project FROM iss_issues ORDER BY project";
$result = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issues List - DSR</title>
</head>
<body>
    <h2>Issues List</h2>
    <a href="add_issue.php" style="font-size: 20px; text-decoration: none;">+ Add Issue</a>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Short Description</th>
            <th>Long Description</th>
            <th>Open Date</th>
            <th>Close Date</th>
            <th>Priority</th>
            <th>Organization</th>
            <th>Project</th>
        </tr>
        <?php while ($row = $result->fetch((PDO::FETCH_ASSOC))) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['id']); ?></td>
            <td><?php echo htmlspecialchars($row['short_description']); ?></td>
            <td><?php echo htmlspecialchars($row['long_description']); ?></td>
            <td><?php echo htmlspecialchars($row['open_date']); ?></td>
            <td><?php echo htmlspecialchars($row['close_date']); ?></td>
            <td><?php echo htmlspecialchars($row['priority']); ?></td>
            <td><?php echo htmlspecialchars($row['org']); ?></td>
            <td><?php echo htmlspecialchars($row['project']); ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>