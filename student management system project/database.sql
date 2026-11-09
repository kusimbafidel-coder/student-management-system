-- Create the student table.
CREATE TABLE IF NOT EXISTS students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_number TEXT NOT NULL UNIQUE,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    phone TEXT,
    programme TEXT NOT NULL,
    year_of_study INTEGER NOT NULL CHECK (year_of_study BETWEEN 1 AND 8),
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

-- Add two example students.
INSERT OR IGNORE INTO students
    (student_number, full_name, email, phone, programme, year_of_study)
VALUES
    ('STU-001', 'Maya Chen', 'maya@example.com', '555-0101', 'Computer Science', 2),
    ('STU-002', 'Omar Ali', 'omar@example.com', '555-0102', 'Information Systems', 1);

-- View all student records.
SELECT * FROM students;

-- Add another student: edit the example values, then run this INSERT.
-- INSERT INTO students (student_number, full_name, email, phone, programme, year_of_study)
-- VALUES ('STU-003', 'Student Name', 'student@example.com', '555-0103', 'Programme', 1);

-- Delete one student: change the number to the record you want to delete.
-- DELETE FROM students WHERE student_number = 'STU-002';
