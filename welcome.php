<?php
session_start();
include 'config.php';

// Check if user is not logged in, then redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the 'add_student' button is clicked
    if (isset($_POST['add_student'])) {
        // Retrieve form data
        $enrollment_number = $_POST['enrollment_number'];
        $name = $_POST['name'];
        $college = $_POST['college'];
        $num_subjects = $_POST['num_subjects'];
        $marks = [];

        for ($i = 1; $i <= $num_subjects; $i++) {
            $marks[] = $_POST["subject{$i}_marks"];
        }

        // Calculate total, average, and status
        $total = array_sum($marks);
        $average = $total / $num_subjects;
        $status = array_reduce($marks, fn($carry, $mark) => $carry && $mark >= 35, true) ? 'Pass' : 'Fail';

        // Convert marks array to JSON
        $marks_json = json_encode($marks);

        // SQL query to insert student data into the database
        $sql = "INSERT INTO students (enrollment_number, name, college, marks, total, average, status)
                VALUES ('$enrollment_number', '$name', '$college', '$marks_json', '$total', '$average', '$status')";

        // Execute the SQL query
        if (mysqli_query($conn, $sql)) {
            echo '<span style="color:black;background-color:blue;display:block;text-align:center;">Student record added successfully</span>';
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }

    // Check if the 'delete_student' button is clicked
    if (isset($_POST['delete_student'])) {
        // Retrieve enrollment number of the student to be deleted
        $enrollment_number = $_POST['enrollment_number'];

        // SQL query to delete student record from the database
        $sql = "DELETE FROM students WHERE enrollment_number='$enrollment_number'";

        // Execute the SQL query
        if (mysqli_query($conn, $sql)) {
            echo '<span style="color:black;background-color:red;display:block;text-align:center;">Student record deleted successfully</span>';
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }

    // Check if the 'edit_student' button is clicked
    if (isset($_POST['edit_student'])) {
        //
                    // Retrieve form data
        $enrollment_number = $_POST['enrollment_number'];
        $name = $_POST['name'];
        $college = $_POST['college'];
        $num_subjects = $_POST['num_subjects'];
        $marks = [];

        for ($i = 1; $i <= $num_subjects; $i++) {
            $marks[] = $_POST["subject{$i}_marks"];
        }

        // Calculate total, average, and status
        $total = array_sum($marks);
        $average = $total / $num_subjects;
        $status = array_reduce($marks, fn($carry, $mark) => $carry && $mark >= 35, true) ? 'Pass' : 'Fail';

        // Convert marks array to JSON
        $marks_json = json_encode($marks);

        // SQL query to update student data in the database
        $sql = "UPDATE students SET name='$name', college='$college', marks='$marks_json', total='$total', average='$average', status='$status'
                WHERE enrollment_number='$enrollment_number'";

        // Execute the SQL query
        if (mysqli_query($conn, $sql)) {
            echo '<span style="color:black;background-color:green;display:block;text-align:center;">Student record updated successfully</span>';
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        @media (max-width: 768px) {
            .col-md-6 {
                width: 100%;
            }
        }
    </style>
    <script>
        function createMarksFields() {
            const numSubjects = document.getElementById('num_subjects').value;
            const marksContainer = document.getElementById('marks_container');
            marksContainer.innerHTML = '';
            for (let i = 1; i <= numSubjects; i++) {
                const div = document.createElement('div');
                div.className = 'form-group';
                div.innerHTML = `<label>Subject ${i} Marks:</label><input type="number" name="subject${i}_marks" class="form-control" required>`;
                marksContainer.appendChild(div);
            }
        }

        function createEditMarksFields() {
            const numSubjects = document.getElementById('edit_num_subjects').value;
            const marksContainer = document.getElementById('edit_marks_container');
            marksContainer.innerHTML = '';
            for (let i = 1; i <= numSubjects; i++) {
                const div = document.createElement('div');
                div.className = 'form-group';
                div.innerHTML = `<label>Subject ${i} Marks:</label><input type="number" name="subject${i}_marks" class="form-control" required>`;
                marksContainer.appendChild(div);
            }
        }

        function populateEditForm(student) {
            const form = document.getElementById('edit_student_form');
            form['enrollment_number'].value = student.enrollment_number;
            form['name'].value = student.name;
            form['college'].value = student.college;
            form['num_subjects'].value = student.marks.length;
            createEditMarksFields();
            for (let i = 0; i < student.marks.length; i++) {
                form[`subject${i + 1}_marks`].value = student.marks[i];
            }
        }
    </script>
</head>
<body>
    <div class="container">
       <center> <h2>Student Management System</h2></center>
        <form method="post" action="">
           <b><u> <h3>Add Student</h3>
            <br>
            <div class="form-group">
               <b><label>Enrollment Number:</label></b>
                <input type="text" name="enrollment_number" class="form-control" required>
            </div>
            <div class="form-group">
               <b> <label>Name:</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
            <b> <label>College:</label>
                <input type="text" name="college" class="form-control" required>
            </div>
            <div class="form-group">
            <b> <label>Number of Subjects:</label>
                <input type="number" id="num_subjects" name="num_subjects" class="form-control" required onchange="createMarksFields()">
            </div>
            <div id="marks_container"></div>
            <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
        </form>
        <br>
        <br>
        <form method="post" action="">
          <b><u>  <h3>Delete Student</h3>
            <br>
            <div class="form-group">
            <b> <label>Enrollment Number:</label>
                <input type="text" name="enrollment_number" class="form-control" required>
            </div>
            <button type="submit" name="delete_student" class="btn btn-danger">Delete Student</button>
        </form>
        <br>
        <br>
       <b><u> <h3>Student Records</h3>
        <br>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Enrollment Number</th>
                    <th>Name</th>
                    <th>College</th>
                    <th>Marks</th>
                    <th>Total</th>
                    <th>Average</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Select all records from students table
                $sql = "SELECT * FROM students";
                $result = mysqli_query($conn, $sql);

                // Check if query executed successfully
                if ($result) {
                    // Loop through each row of the result
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Display student data in table rows
                        $marks = json_decode($row['marks']);
                        $marks_display = implode(", ", $marks);
                        echo "<tr>
                                <td>{$row['enrollment_number']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['college']}</td>
                                <td>{$marks_display}</td>
                                <td>{$row['total']}</td>
                                <td>{$row['average']}</td>
                                <td>{$row['status']}</td>
                                <td>
                                    <button class='btn btn-info' onclick='populateEditForm(" . json_encode($row) . ")'>Edit</button>
                                    <button class='btn btn-secondary' onclick='alert(\"Marks: {$marks_display}\")'>View</button>
                                </td>
                              </tr>";
                    }
                } else {
                    // Display error if query failed
                    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
                }
                ?>
            </tbody>
        </table>
            <BR>
            <BR>
        <form method="post" action="" id="edit_student_form">
            <b><u><h3>Edit Student</h3>
            <BR>
            <div class="form-group">
            <b> <label>Enrollment Number:</label>
                <input type="text" name="enrollment_number" class="form-control" required readonly>
            </div>
            <div class="form-group">
            <b><label>Name:</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
            <b> <label>College:</label>
                <input type="text" name="college" class="form-control" required>
            </div>
            <div class="form-group">
            <b> <label>Number of Subjects:</label>
                <input type="number" id="edit_num_subjects" name="num_subjects" class="form-control" required onchange="createEditMarksFields()">
            </div>
            <div id="edit_marks_container"></div>
            <button type="submit" name="edit_student" class="btn btn-warning">Update Student</button>
        </form>
    </div>
</body>
</html>
