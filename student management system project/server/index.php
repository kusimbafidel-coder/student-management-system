<?php
include __DIR__ . "/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_student"])) {
    $name = trim($_POST["student_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $date = $_POST["enrollment_date"] ?? "";

    if ($name == "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || $date == "") {
        $message = "Enter a name, a valid email address, and an enrollment date.";
    } else {
        $statement = $conn->prepare(
            "INSERT INTO students (student_name, email, enrollment_date) VALUES (?, ?, ?)"
        );

        if ($statement === false) {
            $message = "Could not prepare the student record.";
        } else {
            $statement->bind_param("sss", $name, $email, $date);
            $message = $statement->execute()
                ? "Student added successfully."
                : "Could not add student: " . $statement->error;
            $statement->close();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_student"])) {
    $id = filter_var($_POST["student_id"] ?? "", FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        $message = "Select a valid student to delete.";
    } else {
        $statement = $conn->prepare("DELETE FROM students WHERE student_id = ?");

        if ($statement === false) {
            $message = "Could not prepare the delete request.";
        } else {
            $statement->bind_param("i", $id);
            $message = $statement->execute() && $statement->affected_rows > 0
                ? "Student and their attendance and exam records deleted."
                : "Student record was not found or could not be deleted.";
            $statement->close();
        }
    }
}

$result = $conn->query("SELECT * FROM students ORDER BY student_id");
if ($result === false) {
    die("Could not read students: " . htmlspecialchars($conn->error));
}

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Students | Student Management System</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header>
        <h1>Student Management System</h1>
        <nav>
            <a href="../index.html">Home</a>
            <a href="index.php">Students</a>
            <a href="courses.php">Courses</a>
            <a href="attendance.php">Attendance</a>
            <a href="results.php">Exam results</a>
        </nav>
    </header>

    <main>
        <section>
            <h2>Add a student</h2>
            <?php if ($message != "") { ?>
                <p class="message" role="status"><?= h($message) ?></p>
            <?php } ?>
            <form method="post">
                <label>Student name
                    <input name="student_name" type="text" maxlength="100" required>
                </label>
                <label>Email
                    <input name="email" type="email" maxlength="100" required>
                </label>
                <label>Enrollment date
                    <input name="enrollment_date" type="date" value="<?= date("Y-m-d") ?>" required>
                </label>
                <button type="submit" name="add_student">Add student</button>
            </form>
        </section>

        <section>
            <h2>Student records</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student name</th>
                            <th>Email</th>
                            <th>Enrollment date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows == 0) { ?>
                            <tr><td colspan="5">No students have been added yet.</td></tr>
                        <?php } ?>
                        <?php while ($student = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= h($student["student_id"]) ?></td>
                                <td><?= h($student["student_name"]) ?></td>
                                <td><?= h($student["email"]) ?></td>
                                <td><?= h($student["enrollment_date"]) ?></td>
                                <td>
                                    <form method="post" onsubmit="return confirm('Delete this student and their related records?');">
                                        <input type="hidden" name="student_id" value="<?= h($student["student_id"]) ?>">
                                        <button type="submit" name="delete_student">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
