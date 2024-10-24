<?php
session_start();
include('includes/config.php');

// Handle filters (exam category, class, and sections)
$exam_category = isset($_POST['exam_category']) ? $_POST['exam_category'] : '';
$class_name = isset($_POST['class_name']) ? $_POST['class_name'] : '';
$sections = isset($_POST['sections']) ? $_POST['sections'] : [];

// Fetch sections based on class via AJAX
if (isset($_POST['fetch_sections']) && isset($_POST['class_name'])) {
    $class_name = $_POST['class_name'];
    $sql_sections = "SELECT DISTINCT Section FROM tblclasses WHERE ClassName = :class_name";
    $query_sections = $dbh->prepare($sql_sections);
    $query_sections->bindParam(':class_name', $class_name, PDO::PARAM_STR);
    $query_sections->execute();
    $sections = $query_sections->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sections as $section) {
        echo '<label class="checkbox-inline"><input type="checkbox" name="sections[]" value="' . $section['Section'] . '"> ' . $section['Section'] . '</label>';
    }
    exit();
}

// Fetch students and their results based on selected filters
if (isset($_POST['fetch_students'])) {
    $exam_category = $_POST['exam_category'];
    $class_name = $_POST['class_name'];
    $sections = $_POST['sections'];

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

    // First Table: Students and Subject-wise Marks
    if (!empty($students)) {
        echo '<h4>Students Subject-wise Marks</h4>';
        echo '<table id="subjectMarksTable" class="table table-striped table-bordered">';
        echo '<thead><tr><th>Student Name</th><th>Class</th><th>Section</th><th>Subject</th><th>Marks</th></tr></thead>';
        echo '<tbody>';
        foreach ($students as $student) {
            echo '<tr>';
            echo '<td>' . $student['StudentName'] . '</td>';
            echo '<td>' . $student['ClassName'] . '</td>';
            echo '<td>' . $student['Section'] . '</td>';
            echo '<td>' . $student['SubjectName'] . '</td>';
            echo '<td>' . $student['marks'] . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';

        // Second Table: Students Total Marks and Percentage
        echo '<h4>Students Total Marks and Percentage</h4>';
        echo '<table id="totalMarksTable" class="table table-striped table-bordered">';
        echo '<thead><tr><th>Student Name</th><th>Class</th><th>Section</th><th>Total Marks</th><th>Percentage</th></tr></thead>';
        echo '<tbody>';

        // Calculate total marks and percentage
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

        // Display total marks and percentage
        foreach ($student_totals as $student_total) {
            $percentage = ($student_total['TotalMarks'] / ($student_total['SubjectCount'] * 100)) * 100;
            echo '<tr>';
            echo '<td>' . $student_total['StudentName'] . '</td>';
            echo '<td>' . $student_total['ClassName'] . '</td>';
            echo '<td>' . $student_total['Section'] . '</td>';
            echo '<td>' . $student_total['TotalMarks'] . '</td>';
            echo '<td>' . round($percentage, 2) . '%</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';

        // Percentage Categories
        $acceptable_students = [];
        $good_students = [];
        $very_good_students = [];
        $excellent_students = [];

        // Categorize students based on percentage
        foreach ($student_totals as $student_total) {
            $percentage = ($student_total['TotalMarks'] / ($student_total['SubjectCount'] * 100)) * 100;

            if ($percentage >= 50 && $percentage <= 65) {
                $acceptable_students[] = $student_total;
            } elseif ($percentage >= 66 && $percentage <= 75) {
                $good_students[] = $student_total;
            } elseif ($percentage >= 76 && $percentage <= 89) {
                $very_good_students[] = $student_total;
            } elseif ($percentage >= 90 && $percentage <= 100) {
                $excellent_students[] = $student_total;
            }
        }

        // Display percentage categories
        function display_percentage_category($category_students, $category_name) {
            echo '<h4>' . $category_name . '</h4>';
            if (empty($category_students)) {
                echo '<p>No students in this category.</p>';
            } else {
                echo '<table class="table table-striped">';
                echo '<thead><tr><th>Student Name</th><th>Class</th><th>Section</th><th>Total Marks</th><th>Percentage</th></tr></thead>';
                echo '<tbody>';
                foreach ($category_students as $student) {
                    $percentage = ($student['TotalMarks'] / ($student['SubjectCount'] * 100)) * 100;
                    echo '<tr>';
                    echo '<td>' . $student['StudentName'] . '</td>';
                    echo '<td>' . $student['ClassName'] . '</td>';
                    echo '<td>' . $student['Section'] . '</td>';
                    echo '<td>' . $student['TotalMarks'] . '</td>';
                    echo '<td>' . round($percentage, 2) . '%</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            }
        }

        // Display tables for each percentage category
        display_percentage_category($acceptable_students, 'Acceptable (50%-65%)');
        display_percentage_category($good_students, 'Good (66%-75%)');
        display_percentage_category($very_good_students, 'Very Good (76%-89%)');
        display_percentage_category($excellent_students, 'Excellent (90%-100%)');

    } else {
        echo 'No students found for the selected class and sections.';
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Reports</title>

    <!-- CSS Includes -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/main.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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
                        <h2 class="title">Advanced Result Reports</h2>

                        <!-- Filters Section -->
                        <form id="filtersForm" method="post" class="form-inline">
                            <div class="form-group">
                                <label for="exam_category">Exam Category:</label>
                                <select name="exam_category" id="exam_category" class="form-control" required>
                                    <option value="">Select Exam Category</option>
                                    <?php
                                    // Fetch exam categories dynamically
                                    $sql_exam_categories = "SELECT DISTINCT exam_category FROM tblresult";
                                    $query_exam_categories = $dbh->prepare($sql_exam_categories);
                                    $query_exam_categories->execute();
                                    $exam_categories = $query_exam_categories->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($exam_categories as $exam) { ?>
                                    <option value="<?php echo $exam['exam_category']; ?>">
                                        <?php echo $exam['exam_category']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="class_name">Class:</label>
                                <select name="class_name" id="class_name" class="form-control" required>
                                    <option value="">Select Class</option>
                                    <?php
                                    // Fetch distinct classes dynamically
                                    $sql_classes = "SELECT DISTINCT ClassName FROM tblclasses";
                                    $query_classes = $dbh->prepare($sql_classes);
                                    $query_classes->execute();
                                    $classes = $query_classes->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($classes as $class) { ?>
                                    <option value="<?php echo $class['ClassName']; ?>">
                                        <?php echo $class['ClassName']; ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="sections">Sections:</label>
                                <div id="sections-container"></div>
                            </div>
                        </form>

                        <!-- Student Results Container -->
                        <div id="students-container"></div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript to fetch sections dynamically and update tables on filter change -->
    <script>
    $(document).ready(function() {
        // Fetch sections dynamically when class is selected
        $('#class_name').change(function() {
            var className = $(this).val();
            if (className != '') {
                $.ajax({
                    url: 'result-reports.php',
                    type: 'POST',
                    data: {
                        fetch_sections: true,
                        class_name: className
                    },
                    success: function(response) {
                        $('#sections-container').html(response);
                    }
                });
            }
        });

        // Fetch students and results when filters are changed
        $('#filtersForm').on('change', 'input, select', function() {
            var examCategory = $('#exam_category').val();
            var className = $('#class_name').val();
            var sections = $('input[name="sections[]"]:checked').map(function() {
                return $(this).val();
            }).get();

            if (examCategory && className && sections.length > 0) {
                $.ajax({
                    url: 'result-reports.php',
                    type: 'POST',
                    data: {
                        fetch_students: true,
                        exam_category: examCategory,
                        class_name: className,
                        sections: sections
                    },
                    success: function(response) {
                        $('#students-container').html(response);
                        $('#subjectMarksTable').DataTable();
                        $('#totalMarksTable').DataTable();
                        $('#topStudentsTable').DataTable();
                        $('#bestStudentsBySubjectTable').DataTable();
                    }
                });
            }
        });
    });
    </script>
</body>

</html>