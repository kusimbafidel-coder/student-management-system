<?php
include __DIR__ . "/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_attendance"])) {
    $studentId = filter_var($_POST["student_id"] ?? "", FILTER_VALIDATE_INT);
    $date = $_POST["attendance_date"] ?? "";
    $status = $_POST["status"] ?? "";

    if ($studentId === false || $studentId < 1 || $date == ""
        || !in_array($status, ["Present", "Absent"], true)) {
        $message = "Select a student, date, and attendance status.";
    } else {
        $statement = $conn->prepare(
            "INSERT INTO attendance (student_id, attendance_date, status) VALUES (?, ?, ?)"
        );

        if ($statement === false) {
            $message = "Could not prepare the attendance record.";
        } else {
            $statement->bind_param("iss", $studentId, $date, $status);
            $message = $statement->execute()
                ? "Attendance added successfully."
                : "Could not add attendance. This student may already have attendance for that date.";
            $statement->close();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_attendance"])) {
    $id = filter_var($_POST["attendance_id"] ?? "", FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        $message = "Select a valid attendance record to delete.";
    } else {
        $statement = $conn->prepare("DELETE FROM attendance WHERE attendance_id = ?");

        if ($statement === false) {
            $message = "Could not prepare the delete request.";
        } else {
            $statement->bind_param("i", $id);
            $message = $statement->execute() && $statement->affected_rows > 0
                ? "Attendance record deleted."
                : "Attendance record was not found or could not be deleted.";
            $statement->close();
        }
    }
}

$students = $conn->query("SELECT student_id, student_name FROM students ORDER BY student_name");
$result = $conn->query(
    "SELECT attendance.attendance_id, students.student_name,
            attendance.attendance_date, attendance.status
     FROM attendance
     JOIN students ON attendance.student_id = students.student_id
     ORDER BY attendance.attendance_date DESC"
);
if ($students === false || $result === false) {
    die("Could not read attendance: " . htmlspecialchars($conn->error));
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
    <title>Attendance | Student Management System</title>
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
            <h2>Record attendance</h2>
            <?php if ($message != "") { ?>
                <p class="message" role="status"><?= h($message) ?></p>
            <?php } ?>
            <?php if ($students->num_rows == 0) { ?>
                <p>Add a student before recording attendance.</p>
            <?php } else { ?>
                <form method="post">
                    <label>Student
                        <select name="student_id" required>
                            <?php while ($student = $students->fetch_assoc()) { ?>
                                <option value="<?= h($student["student_id"]) ?>"><?= h($student["student_name"]) ?></option>
                            <?php } ?>
                        </select>
                    </label>
                    <label>Date
                        <input name="attendance_date" type="date" value="<?= date("Y-m-d") ?>" required>
                    </label>
                    <label>Status
                        <select name="status" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                        </select>
                    </label>
                    <button type="submit" name="add_attendance">Save attendance</button>
                </form>
            <?php } ?>
        </section>

        <section>
            <h2>Attendance records</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Student</th><th>Date</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows == 0) { ?>
                            <tr><td colspan="4">No attendance records have been added yet.</td></tr>
                        <?php } ?>
                        <?php while ($record = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?= h($record["student_name"]) ?></td>
                                <td><?= h($record["attendance_date"]) ?></td>
                                <td><?= h($record["status"]) ?></td>
                                <td>
                                    <form method="post" onsubmit="return confirm('Delete this attendance record?');">
                                        <input type="hidden" name="attendance_id" value="<?= h($record["attendance_id"]) ?>">
                                        <button type="submit" name="delete_attendance">Delete</button>
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
