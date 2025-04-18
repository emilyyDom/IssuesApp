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

// Handle issue operations (Add, Update, Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if(isset($_FILES['pdf_attachment'])){
        //variable names for details needed to pdf array
        $fileTmpPath = $_FILES['pdf_attachment']['tmp_name'];
        $fileName    = $_FILES['pdf_attachment']['name'];
        $fileSize    = $_FILES['pdf_attachment']['size'];
        $fileType    = $_FILES['pdf_attachment']['type'];
        //find extendtion aka type type making sure its .pdf
        $fileNameCmps = explode(".", $fileName);
        //converting extention to tolower
        $fileExtension = strtolower ( end ($fileNameCmps) ) ;

        //checking file type and within size limit
        if($fileExtension !== 'pdf'){
            die("Only PDF files are allowed");
        }

        if($fileSize > 2 * 1024 * 1024){
            die("File size exceeds 2MB limit");
        }
        //the real file name was the file name that came to us-- this includes the time for a specfic identifyer
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        //where the pdf files are stored
        $uploadFileDir = './uploads/';
        $dest_path = $uploadFileDir . $newFileName;
        
        //if no subdirectory/ uploads directory found make it
        if(!is_dir($uploadFileDir)){
            //make sub directory, allows for all people to read and use it
            mkdir($uploadFileDir, 0755, true);
        }

        if(move_uploaded_file($fileTmpPath, $dest_path)){
            $attachmentPath = $dest_path;
        } else{
            die("error moving file");
        }
    } //end pdf attachment


    // Add Issue
    if (isset($_POST['add_issue'])) {
        $short_description = trim($_POST['short_description']);
        $long_description = trim($_POST['long_description']);
        $open_date = $_POST['open_date'];
        $close_date = $_POST['close_date'];
        $priority = $_POST['priority'];
        $org = trim($_POST['org']);
        $project = trim($_POST['project']);
        $per_id = $_POST['per_id'];
        //$newFileName is PDF attachment
        //$attachmentPath is the entire path
        //$newFileName = $POST['pdf_attachment'];

        $sql = "INSERT INTO iss_issues (short_description, long_description, open_date, close_date, priority, org, project, per_id, pdf_attachment) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$short_description, $long_description, $open_date, $close_date, $priority, $org, $project, $per_id, $newFileName]);

        header("Location: issues_list.php");
        exit();
    }

    // Update Issue
    if (isset($_POST['update_issue'])) {
        if( !($_SESSION['admin'] == "Y" || $_SESSION['user_id'] == $_POST['per_id']) ) {
            header("Location: issues_list.php");
            exit();
        }   
        $id = $_POST['id'];
        $short_description = trim($_POST['short_description']);
        $long_description = trim($_POST['long_description']);
        $open_date = $_POST['open_date'];
        $close_date = $_POST['close_date'];
        $priority = $_POST['priority'];
        $org = trim($_POST['org']);
        $project = trim($_POST['project']);
        $per_id = $_POST['per_id'];

        $sql = "UPDATE iss_issues SET short_description=?, long_description=?, open_date=?, close_date=?, priority=?, org=?, project=?, per_id=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$short_description, $long_description, $open_date, $close_date, $priority, $org, $project, $per_id, $id]);

        header("Location: issues_list.php");
        exit();
    }

    // Delete Issue
    if (isset($_POST['delete_issue'])) {
        if( !($_SESSION['admin'] == "Y" || $_SESSION['user_id'] == $_POST['per_id']) ){
            header("Location: issues_list.php");
             exit();
        }
        $id = $_POST['id'];
        $sql = "DELETE FROM iss_issues WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        header("Location: issues_list.php");
        exit();
    }
}
/*Just added to check for toggle*/
// Check if the user wants to view all issues or just open ones
$view_all_issues = isset($_POST['view_all']) ? $_POST['view_all'] : 'no';

// Modify SQL query based on the toggle
if ($view_all_issues == 'yes') {
    $sql = "SELECT * FROM iss_issues ORDER BY open_date DESC";
} else {
    $sql = "SELECT * FROM iss_issues WHERE close_date IS NULL ORDER BY open_date DESC"; // Only open issues
}

$issues = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);



// Fetch all issues
//$sql = "SELECT * FROM iss_issues ORDER BY open_date DESC";
//$issues = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issues List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-3">
        <h2 class="text-center">Issues List</h2>

        <!-- "Show (all) issues" Button -->
        <form method="POST" action="issues_list.php">
            <button type="submit" name="view_all" value="<?php echo ($view_all_issues == 'yes') ? 'no' : 'yes'; ?>" class="btn btn-secondary">
                <?php echo ($view_all_issues == 'yes') ? 'Show Open Issues' : 'Show All Issues'; ?>
            </button>

                <!-- "logout" Button -->
            <a href= "logout.php" class="btn btn-warning"> Logout</a>
            
        </form>

       
        <!-- "+" Button to Add Issue -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <h3> <?php echo ($view_all_issues === 'yes') ? 'All Issues' : 'Open Issues'; ?></h3>
            <a href="persons_list.php" class="btn btn-primary">People</a>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addIssueModal">+</button> 
        </div>

        <!-- Add Issue Modal -->
        <div class="modal fade" id="addIssueModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Issue</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="text" name="short_description" class="form-control mb-2" placeholder="Short Description" required>
                            <textarea name="long_description" class="form-control mb-2" placeholder="Long Description"></textarea>
                            <input type="date" name="open_date" class="form-control mb-2" required>
                            <input type="date" name="close_date" class="form-control mb-2">
                            <select name="priority" class="form-control mb-2">
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="F">F</option>
                            </select>
                            <input type="text" name="org" class="form-control mb-2" placeholder="Organization">
                            <input type="text" name="project" class="form-control mb-2" placeholder="Project">
                            <select name="per_id" class="form-control mb-2">
                                <option value="">Select Person</option>
                                <?php foreach ($persons as $person) : ?>
                                    <option value="<?= $person['id']; ?>"><?= htmlspecialchars($person['fname'] . " " . $person['lname']); ?></option>
                                <?php endforeach; ?>
                            </select>
                                </label for="pdf_attachment">PDF</label>
                                <input type="file" name="pdf_attachment" class="form-control mb-2" accept="application/pdf"/>

                            <button type="submit" name="add_issue" class="btn btn-primary">Add Issue</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <table class="table table-striped table-sm mt-2">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Short Description</th>
                    <th>Open Date</th>
                    <th>Close Date</th>
                    <th>Priority</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($issues as $issue) : ?>
                    <tr>
                        <td><?= htmlspecialchars($issue['id']); ?></td>
                        <td><?= htmlspecialchars($issue['short_description']); ?></td>
                        <td><?= htmlspecialchars($issue['open_date']); ?></td>
                        <td><?= htmlspecialchars($issue['close_date']); ?></td>
                        <td><?= htmlspecialchars($issue['priority']); ?></td>
                        <td>
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#readIssue<?= $issue['id']; ?>">R</button>
                            <?php if($_SESSION['user_id'] == $issue['per_id'] || $_SESSION['admin'] == "Y" ) {?>
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#updateIssue<?= $issue['id']; ?>">U</button>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteIssue<?= $issue['id']; ?>">D</button>
                            <?php } ?>

                        </td>
                    </tr>

                    <!-- Read Modal -->
                    <div class="modal fade" id="readIssue<?= $issue['id']; ?>" tabindex="-1">
                        <?php $_SESSION['issue_id'] = $issue['id']; ?>
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Issue Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>ID:</strong> <?= htmlspecialchars($issue['id']); ?></p>
                                    <p><strong>Short Description:</strong> <?= htmlspecialchars($issue['short_description']); ?></p>
                                    <p><strong>Long Description:</strong> <?= htmlspecialchars($issue['long_description']); ?></p>
                                    <p><strong>Open Date:</strong> <?= htmlspecialchars($issue['open_date']); ?></p>
                                    <p><strong>Close Date:</strong> <?= htmlspecialchars($issue['close_date']); ?></p>
                                    <p><strong>Priority:</strong> <?= htmlspecialchars($issue['priority']); ?></p>
                                    <p><strong>Organization:</strong> <?= htmlspecialchars($issue['org']); ?></p>
                                    <p><strong>Project:</strong> <?= htmlspecialchars($issue['project']); ?></p>
                                    <p><strong>Person ID:</strong> <?= htmlspecialchars($issue['per_id']); ?></p>

                                    <!-- Comments Section -->
                                     <h5>Comments</h5>
                                     <a href="comments_list.php?action=create&issue_id=<?= $issue['id']; ?>" class="btn btn-success btn-sm">+</a>
                                    <?php
                                    $com_iss_id = $issue['id'];
                                    // Fetch comments for this particular issue
                                    $comments_sql = "SELECT * FROM iss_comments, iss_persons 
                                            WHERE iss_id = $com_iss_id
                                            AND `iss_persons`.id = per_id
                                            ORDER BY posted_date DESC";
                                        $comments_stmt = $pdo->query($comments_sql);
                                        $comments = $comments_stmt->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                    
                                    <?php foreach ($comments as $comment) : ?>
                                        <div style="font-family: monospace; margin-bottom: 10px">
                                            <span style="display:inline-block; width: 180px;">
                                                <?= htmlspecialchars($comment['lname'] . ", " . $comment['fname']) ?>
                                            </span>
                                            <span style="display:inline-block; width: 300px;">
                                                <?= htmlspecialchars($comment['short_comment']) ?>
                                            </span>
                                            <span style="display:inline-block; width: 140px;">
                                                <?= htmlspecialchars($comment['posted_date']) ?>
                                            </span>
                                            <span style="display:inline-block; width: 150px;">
                                               
                                                <a href="comments_list.php?action=read&issue_id=<?= $issue['id']; ?>" class="btn btn-info btn-sm">R</a>
                                                <?php if($_SESSION['user_id'] == $issue['per_id'] || $_SESSION['admin'] == "Y" ) {?>
                                                <a href="comments_list.php?action=update&issue_id=<?= $issue['id']; ?>" class="btn btn-warning btn-sm">U</a>
                                                <a href="comments_list.php?action=delete&issue_id=<?= $issue['id']; ?>" class="btn btn-danger btn-sm">D</a>
                                                <?php } ?>
                                            </span>

                                        </div>


                                        
                                    <?php endforeach; ?>
                                   

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Modal -->
                    <div class="modal fade" id="updateIssue<?= $issue['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Issue</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $issue['id']; ?>">
                                        <input type="text" name="short_description" class="form-control mb-2" value="<?= htmlspecialchars($issue['short_description']); ?>" required>
                                        <textarea name="long_description" class="form-control mb-2"><?= htmlspecialchars($issue['long_description']); ?></textarea>
                                        <input type="date" name="open_date" class="form-control mb-2" value="<?= $issue['open_date']; ?>" required>
                                        <input type="date" name="close_date" class="form-control mb-2" value="<?= $issue['close_date']; ?>">
                                        <select name="priority" class="form-control mb-2">
                                            <option value="Low" <?= $issue['priority'] == 'Low' ? 'selected' : ''; ?>>Low</option>
                                            <option value="Medium" <?= $issue['priority'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                            <option value="High" <?= $issue['priority'] == 'High' ? 'selected' : ''; ?>>High</option>
                                        </select>
                                        <input type="text" name="org" class="form-control mb-2" value="<?= htmlspecialchars($issue['org']); ?>" placeholder="Organization">
                                        <input type="text" name="project" class="form-control mb-2" value="<?= htmlspecialchars($issue['project']); ?>" placeholder="Project">
                                        <select name="per_id" class="form-control mb-2">
                                            <option value="">Select Person</option>
                                            <?php foreach ($persons as $person) : ?>
                                                <option value="<?= $person['id']; ?>" <?= $issue['per_id'] == $person['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($person['fname'] . " " . $person['lname']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="update_issue" class="btn btn-primary">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteIssue<?= $issue['id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Confirm Deletion</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Are you sure you want to delete this issue?</p>
                                    <p><strong>ID:</strong> <?= htmlspecialchars($issue['id']); ?></p>
                                    <p><strong>Short Description:</strong> <?= htmlspecialchars($issue['short_description']); ?></p>
                                    <p><strong>Long Description:</strong> <?= htmlspecialchars($issue['long_description']); ?></p>
                                    <p><strong>Organization:</strong> <?= htmlspecialchars($issue['org']); ?></p>
                                    <p><strong>Project:</strong> <?= htmlspecialchars($issue['project']); ?></p>

                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $issue['id']; ?>">
                                        <button type="submit" name="delete_issue" class="btn btn-danger">Delete</button>
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
