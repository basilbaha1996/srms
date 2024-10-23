<?php
session_start();
include('includes/config.php');

// Handle filters (exam category, class, and sections)
$exam_category = isset($_POST['exam_category']) ? $_POST['exam_category'] : '';
$class_name = isset($_POST['class_name']) ? $_POST['class_name'] : '';
$sections = isset($_POST['sections']) ? $_POST['sections'] : [];

// Fetch exam categories
$sql_exam_categories = "SELECT DISTINCT exam_category FROM tblresult";
$query_exam_categories = $dbh->prepare($sql_exam_categories);
$query_exam_categories->execute();
$exam_categories = $query_exam_categories->fetchAll(PDO::FETCH_ASSOC);

// Fetch distinct classes (without duplication of class names)
$sql_classes = "SELECT DISTINCT ClassName FROM tblclasses";
$query_classes = $dbh->prepare($sql_classes);
$query_classes->execute();
$classes = $query_classes->fetchAll(PDO::FETCH_ASSOC);

// Fetch sections based on selected class name
$sections_available = [];
if ($class_name != '') {
    $sql_sections = "SELECT DISTINCT Section FROM tblclasses WHERE ClassName = :class_name";
    $query_sections = $dbh->prepare($sql_sections);
    $query_sections->bindParam(':class_name', $class_name, PDO::PARAM_STR);
    $query_sections->execute();
    $sections_available = $query_sections->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch students and their results based on selected filters
$students = [];
if ($exam_category && $class_name && !empty($sections)) {
    $section_filter = "'" . implode("','", $sections) . "'";
    $sql_students = "SELECT tblstudents.StudentName, tblclasses.ClassName, tblclasses.Section, tblsubjects.SubjectName, tblresult.marks 
        FROM tblresult
        JOIN tblstudents ON tblresult.StudentId = tblstudents.StudentId
        JOIN tblclasses ON tblstudents.ClassId = tblclasses.id
        JOIN tblsubjects ON tblresult.SubjectId = tblsubjects.id
        WHERE tblclasses.ClassName = :class_name 
        AND tblresult.exam_category = :exam_category 
        AND tblclasses.Section IN ($section_filter)";
    $query_students = $dbh->prepare($sql_students);
    $query_students->bindParam(':class_name', $class_name, PDO::PARAM_STR);
    $query_students->bindParam(':exam_category', $exam_category, PDO::PARAM_STR);
    $query_students->execute();
    $students = $query_students->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Reports</title>

    <!-- CSS Includes (Make sure they match your other reports) -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/main.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <style>
    .filter-active {
        background-color: #3498db;
        color: white;
    }
    </style>
</head>

<body class="top-navbar-fixed">
    <div class="main-wrapper">
        <?php include('includes/topbar.php'); ?>
        <div class="content-wrapper">
            <div class="content-container">
                <?php include('includes/leftbar.php'); ?>
                <div class="main-page">
                    <div class="container-fluid">
                        <h2 class="title">Advanced Student Reports</h2>

                        <!-- Filters Section -->
                        <form method="post" class="form-inline">
                            <div class="form-group">
                                <label for="exam_category">Exam Category:</label>
                                <select name="exam_category" class="form-control" required>
                                    <option value="">Select Exam Category</option>
                                    <?php foreach ($exam_categories as $exam) { ?>
                                    <option value="<?php echo $exam['exam_category']; ?>"
                                        <?php if ($exam_category == $exam['exam_category']) echo 'selected'; ?>>
                                        <?php echo $exam['exam_category']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="class_name">Class:</label>
                                <select name="class_name" id="class_name" class="form-control" required>
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $class) { ?>
                                    <option value="<?php echo $class['ClassName']; ?>"
                                        <?php if ($class_name == $class['ClassName']) echo 'selected'; ?>>
                                        <?php echo $class['ClassName']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="sections">Sections:</label>
                                <?php foreach ($sections_available as $section) { ?>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="sections[]" value="<?php echo $section['Section']; ?>"
                                        <?php if (in_array($section['Section'], $sections)) echo 'checked'; ?>>
                                    <?php echo $section['Section']; ?>
                                </label>
                                <?php } ?>
                            </div>

                            <button type="submit" class="btn btn-primary">Filter</button>
                        </form>

                        <!-- Reports Section -->
                        <?php if (!empty($students)) { ?>
                        <h4>Students Subject-wise Marks</h4>
                        <table id="subjectMarksTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Subject</th>
                                    <th>Marks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student) { ?>
                                <tr>
                                    <td><?php echo $student['StudentName']; ?></td>
                                    <td><?php echo $student['ClassName']; ?></td>
                                    <td><?php echo $student['Section']; ?></td>
                                    <td><?php echo $student['SubjectName']; ?></td>
                                    <td><?php echo $student['marks']; ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <!-- Total Marks and Percentage -->
                        <h4>Students Total Marks and Percentage</h4>
                        <table id="totalMarksTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Total Marks</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    // To calculate total marks and percentage for each student
                                    $student_totals = [];
                                    foreach ($students as $student) {
                                        if (!isset($student_totals[$student['StudentName']])) {
                                            $student_totals[$student['StudentName']] = [
                                                'StudentName' => $student['StudentName'],
                                                'ClassName' => $student['ClassName'],
                                                'Section' => $student['Section'],
                                                'TotalMarks' => 0,
                                                'SubjectCount' => 0
                                            ];
                                        }
                                        $student_totals[$student['StudentName']]['TotalMarks'] += $student['marks'];
                                        $student_totals[$student['StudentName']]['SubjectCount']++;
                                    }

                                    // Display students' total marks and percentage
                                    foreach ($student_totals as $student_total) {
                                        $percentage = ($student_total['TotalMarks'] / ($student_total['SubjectCount'] * 100)) * 100;
                                        ?>
                                <tr>
                                    <td><?php echo $student_total['StudentName']; ?></td>
                                    <td><?php echo $student_total['ClassName']; ?></td>
                                    <td><?php echo $student_total['Section']; ?></td>
                                    <td><?php echo $student_total['TotalMarks']; ?></td>
                                    <td><?php echo round($percentage, 2); ?>%</td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Initialize DataTables for better sorting and searching -->
    <script>
    $(document).ready(function() {
        $('#subjectMarksTable').DataTable();
        $('#totalMarksTable').DataTable();
    });
    </script>
</body>

</html>