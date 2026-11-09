<?php
include __DIR__ . "/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_course"])) {
    $code = trim($_POST["course_code"] ?? "");
    $name = trim($_POST["course_name"] ?? "");

    if ($code == "" || $name == "") {
        $message = "Enter a course code and course name.";
    } else {
        $statement = $conn->prepare(
            "INSERT INTO courses (course_code, course_name) VALUES (?, ?)"
        );

        if ($statement === false) {
            $message = "Could not prepare the course record.";
        } else {
            $statement->bind_param("ss", $code, $name);
            $message = $statement->execute()
                ? "Course added successfully."
                : "Could not add course: " . $statement->error;
            $statement->close();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_course"])) {
    $id = filter_var($_POST["course_id"] ?? "", FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        $message = "Select a valid course to delete.";
    } else {
        $statement = $conn->prepare("DELETE FROM courses WHERE course_id = ?");

        if ($statement === false) {
            $message = "Could not prepare the delete request.";
        } else {
            $statement->bind_param("i", $id);
            $message = $statement->execute() && $statement->affected_rows > 0
                ? "Course and its exam results deleted."
                : "Course record was not found or could not be deleted.";
            $statement->close();
        }
    }
}

$result = $conn->query("SELECT * FROM courses ORDER BY course_id");
if ($result === false) {
    die("Could not read courses: " . htmlspecialchars($conn->error));
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
    <title>Courses | Student Management System</title>
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
            <h2>Add a course</h2>
            <?php if ($message != "") { ?>
                <p class="message" role="status"><?= h($message) ?></p>
            <?php } ?>
            <form method="post">
                <label>Course code
                    <input name="course_code" type="text" maxlength="20" placeholder="e.g. CSC101" required>
                </label>
                <label>Course name
                    <input name="course_name" type="text" maxlength="100" placeholder="e.g. Computer Science" required>
                </label>
                <button type="submit" name="add_course">Add course</button>
            </form>
        </section>

        <section>
            <h2>Course records</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Course ID</th><th>Course code</th><th>Course name</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows == 0) { ?>
                            <tr><td colspan="4">No courses have been added yet.</td></tr>
                        <?php } ?>
                        <?php while ($course = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= h($course["course_id"]) ?></td>
                                <td><?= h($course["course_code"]) ?></td>
                                <td><?= h($course["course_name"]) ?></td>
                                <td>
                                    <form method="post" onsubmit="return confirm('Delete this course and its exam results?');">
                                        <input type="hidden" name="course_id" value="<?= h($course["course_id"]) ?>">
                                        <button type="submit" name="delete_course">Delete</button>
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
