# Student Management System

The website uses HTML and CSS, PHP, and the MySQL database included with XAMPP. It has four related tables and pages for students, courses, attendance, and exam results. Each page lets you add, view, and delete records.

## Run the project

1. Copy the project folder into XAMPP's `htdocs` folder. For example: `C:\xampp\htdocs\student-management-system`.
2. Open the XAMPP Control Panel and click **Start** next to **Apache** and **MySQL**.
3. Open `http://localhost/phpmyadmin` in your browser.
4. In phpMyAdmin, select **Import**, choose this project's `database.sql` file, then click **Import** or **Go**. The script creates the `student_management` database and its four tables.
5. Open `http://localhost/student-management-system/` and use the navigation links.
6. Add a student and a course before recording attendance or exam results. Each page shows the records saved in the database and has a **Delete** button.

If you named the project folder differently, use that name in the address. Do not double-click the PHP or HTML files; open the website using its `http://localhost/...` address so Apache can run PHP.

If clicking **Students**, **Courses**, **Attendance**, or **Exam results** downloads a `.php` file, check the browser address. If it starts with `file://`, you opened the project directly instead of through XAMPP. Start Apache, copy the folder into `htdocs`, and open `http://localhost/<folder-name>/`. If the address already starts with `http://localhost/` and PHP still downloads, restart Apache in XAMPP and make sure you are using XAMPP's Apache server.

If you already imported the earlier, student-only version of `database.sql`, the database already has an older `students` table. Back up any records you need, then delete the old `student_management` database in phpMyAdmin before importing this four-table version. `CREATE TABLE IF NOT EXISTS` does not replace an older table.

## Database connection

The default XAMPP connection is `localhost`, user `root`, and a blank password, with database `student_management`. These defaults match a typical local XAMPP installation. If you have set a MySQL root password, update `server/database.php`.

The pages in `server/` manage the four MySQL tables, based on the Access example. Prepared statements are used for adding and deleting records. Attendance and exam results refer to student IDs; exam results also refer to course IDs, so student and course names stay in one place.
