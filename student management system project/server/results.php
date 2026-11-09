<?php
include __DIR__ . "/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_result"])) {
    $studentId = filter_var($_POST["student_id"] ?? "", FILTER_VALIDATE_INT);
    $courseId = filter_var($_POST["course_id"] ?? "", FILTER_VALIDATE_INT);
    $date = $_POST["exam_date"] ?? "";
    $grade = trim($_POST["grade"] ?? "");
    $marksValue = $_POST["marks"] ?? "";
    $marks = is_numeric($marksValue) ? (float) $marksValue : -1;

    if ($studentId === false || $studentId < 1 || $courseId === false || $courseId < 1
        || $date == "" || $grade == "" || strlen($grade) > 5 || $marks < 0 || $marks > 100) {
        $message = "Enter a student, course, date, grade, and marks from 0 to 100.";
    } else {
        $statement = $conn->prepare(
            "INSERT INTO exam_results (student_id, course_id, exam_date, grade, marks)
             VALUES (?, ?, ?, ?, ?)"
        );

        if ($statement === false) {
            $message = "Could not prepare the exam result.";
        } else {
            $statement->bind_param("iissd", $studentId, $courseId, $date, $grade, $marks);
            $message = $statement->execute()
                ? "Exam result added successfully."
                : "Could not add the exam result: " . $statement->error;
            $statement->close();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_result"])) {
    $id = filter_var($_POST["exam_result_id"] ?? "", FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        $message = "Select a valid exam result to delete.";
    } else {
        $statement = $conn->prepare("DELETE FROM exam_results WHERE exam_result_id = ?");

        if ($statement === false) {
            $message = "Could not prepare the delete request.";
        } else {
            $statement->bind_param("i", $id);
            $message = $statement->execute() && $statement->affected_rows > 0
                ? "Exam result deleted."
                : "Exam result was not found or could not be deleted.";
            $statement->close();
        }
    }
}

$students = $conn->query("SELECT student_id, student_name FROM students ORDER BY student_name");
$courses = $conn->query("SELECT course_id, course_code, course_name FROM courses ORDER BY course_code");
$result = $conn->query(
    "SELECT exam_results.exam_result_id, students.student_name,
            courses.course_code, courses.course_name, exam_results.exam_date,
            exam_results.grade, exam_results.marks
     FROM exam_results
     JOIN students ON exam_results.student_id = students.student_id
     JOIN courses ON exam_results.course_id = courses.course_id
     ORDER BY exam_results.exam_date DESC"
);
if ($students === false || $courses === false || $result === false) {
    die("Could not read exam results: " . htmlspecialchars($conn->error));
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
    <title>Exam results | Student Management System</title>
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
            <h2>Add an exam result</h2>
            <?php if ($message != "") { ?>
                <p class="message" role="status"><?= h($message) ?></p>
            <?php } ?>
            <?php if ($students->num_rows == 0 || $courses->num_rows == 0) { ?>
                <p>Add at least one student and one course before recording an exam result.</p>
            <?php } else { ?>
                <form method="post">
                    <label>Student
                        <select name="student_id" required>
                            <?php while ($student = $students->fetch_assoc()) { ?>
                                <option value="<?= h($student["student_id"]) ?>"><?= h($student["student_name"]) ?></option>
                            <?php } ?>
                        </select>
                    </label>
                    <label>Course
                        <select name="course_id" required>
                            <?php while ($course = $courses->fetch_assoc()) { ?>
                                <option value="<?= h($course["course_id"]) ?>"><?= h($course["course_code"]) ?> - <?= h($course["course_name"]) ?></option>
                            <?php } ?>
                        </select>
                    </label>
                    <label>Exam date
                        <input name="exam_date" type="date" value="<?= date("Y-m-d") ?>" required>
                    </label>
                    <label>Grade
                        <input name="grade" type="text" maxlength="5" placeholder="e.g. A" required>
                    </label>
                    <label>Marks (0-100)
                        <input name="marks" type="number" min="0" max="100" step="0.01" required>
                    </label>
                    <button type="submit" name="add_result">Save exam result</button>
                </form>
            <?php } ?>
        </section>

        <section>
            <h2>Exam results</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Student</th><th>Course code</th><th>Course</th><th>Exam date</th><th>Grade</th><th>Marks</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows == 0) { ?>
                            <tr><td colspan="7">No exam results have been added yet.</td></tr>
                        <?php } ?>
                        <?php while ($record = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= h($record["student_name"]) ?></td>
                                <td><?= h($record["course_code"]) ?></td>
                                <td><?= h($record["course_name"]) ?></td>
                                <td><?= h($record["exam_date"]) ?></td>
                                <td><?= h($record["grade"]) ?></td>
                                <td><?= h($record["marks"]) ?></td>
                                <td>
                                    <form method="post" onsubmit="return confirm('Delete this exam result?');">
                                        <input type="hidden" name="exam_result_id" value="<?= h($record["exam_result_id"]) ?>">
                                        <button type="submit" name="delete_result">Delete</button>
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
