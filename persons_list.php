<?php
session_start();
require 'database.php'; // Database connection

$pdo = Database::connect();
$error_message = "";

// Fetch all persons
$sql = "SELECT * FROM iss_persons ORDER BY lname ASC";
$persons = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Handle Create, Update, Delete actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_person'])) {
        $fname = trim($_POST['fname']);
        $lname = trim($_POST['lname']);
        $mobile = trim($_POST['mobile']);
        $email = trim($_POST['email']);
        $pwd_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $admin = isset($_POST['admin']) ? 1 : 0;

        $sql = "INSERT INTO iss_persons (fname, lname, mobile, email, pwd_hash, admin) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$fname, $lname, $mobile, $email, $pwd_hash, $admin]);

        header("Location: persons_list.php");
        exit();
    }

    if (isset($_POST['update_person'])) {
        $id = $_POST['id'];
        $fname = trim($_POST['fname']);
        $lname = trim($_POST['lname']);
        $mobile = trim($_POST['mobile']);
        $email = trim($_POST['email']);
        $admin = isset($_POST['admin']) ? 1 : 0;

        $sql = "UPDATE iss_persons SET fname=?, lname=?, mobile=?, email=?, admin=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$fname, $lname, $mobile, $email, $admin, $id]);

        header("Location: persons_list.php");
        exit();
    }

    if (isset($_POST['delete_person'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM iss_persons WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        header("Location: persons_list.php");
        exit();
    }
}

// Fetch all persons
$sql = "SELECT * FROM iss_persons ORDER BY id DESC";
$persons = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>People List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-3">
        <h2 class="text-center">People List</h2>

        <!-- Buttons -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <a href="issues_list.php" class="btn btn-secondary">Back to Issues</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPersonModal">+</button>
        </div>

        <!-- Add Issue Modal -->
        <div class="modal fade" id="addPersonModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Person</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= $person['id']; ?>">
                            <input type="text" name="fname" class="form-control mb-2" placeholder="First Name" required>
                            <input type="text" name="lname" class="form-control mb-2" placeholder="Last Name" required>
                            <input type="text" name="mobile" class="form-control mb-2" placeholder="mobile phone number" required>
                            <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
                            <button type="submit" name="add_person" class="btn btn-primary">Add Person</button>
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
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Admin</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($persons as $person) : ?>
                    <tr>
                        <td><?= htmlspecialchars($person['id']); ?></td>
                        <td><?= htmlspecialchars($person['fname'] . ' ' . $person['lname']); ?></td>
                        <td><?= htmlspecialchars($person['mobile']); ?></td>
                        <td><?= htmlspecialchars($person['email']); ?></td>
                        <td><?= $person['admin'] ? 'Yes' : 'No'; ?></td>
                        <td>
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#readPerson<?= $person['id']; ?>">R</button>
                            <?php if($_SESSION['user_id'] == $person['id'] || $_SESSION['admin'] == "Y" ) {?>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updatePerson<?= $person['id']; ?>">U</button>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deletePerson<?= $person['id']; ?>">D</button>
                            <?php } ?>
                        </td>
                    </tr>

                    <!-- Read Modal -->
                    <div class="modal fade" id="readPerson<?= $person['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Person Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Full Name:</strong> <?= htmlspecialchars($person['fname'] . ' ' . $person['lname']); ?></p>
                                    <p><strong>Mobile:</strong> <?= htmlspecialchars($person['mobile']); ?></p>
                                    <p><strong>Email:</strong> <?= htmlspecialchars($person['email']); ?></p>
                                    <p><strong>Admin:</strong> <?= $person['admin'] ? 'Yes' : 'No'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Modal -->
                    <div class="modal fade" id="updatePerson<?= $person['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Person</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $person['id']; ?>">
                                        <input type="text" name="fname" class="form-control mb-2" value="<?= htmlspecialchars($person['fname']); ?>" required>
                                        <input type="text" name="lname" class="form-control mb-2" value="<?= htmlspecialchars($person['lname']); ?>" required>
                                        <input type="text" name="mobile" class="form-control mb-2" value="<?= htmlspecialchars($person['mobile']); ?>" required>
                                        <input type="email" name="email" class="form-control mb-2" value="<?= htmlspecialchars($person['email']); ?>" required>
                                        <input type="checkbox" name="admin" <?= $person['admin'] ? 'checked' : ''; ?>> Admin
                                        <button type="submit" name="update_person" class="btn btn-primary mt-2">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deletePerson<?= $person['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Confirm Deletion</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete this person?</p>
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $person['id']; ?>">
                                        <button type="submit" name="delete_person" class="btn btn-danger">Delete</button>
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
