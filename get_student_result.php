<?php
include('includes/config.php');

$stid = intval($_POST['stid']);
$exam_category = intval($_POST['exam_category']); // Assuming this is the category ID, not the name

// Fetch student details and results
$ret = "SELECT tblstudents.StudentName, tblclasses.ClassName, tblclasses.Section 
        FROM tblresult 
        JOIN tblstudents ON tblresult.StudentId = tblstudents.StudentId 
        JOIN tblclasses ON tblclasses.id = tblstudents.ClassId 
        WHERE tblstudents.StudentId = :stid 
        LIMIT 1";
$stmt = $dbh->prepare($ret);
$stmt->bindParam(':stid', $stid, PDO::PARAM_STR);
$stmt->execute();
$studentDetails = $stmt->fetch(PDO::FETCH_OBJ);

if($studentDetails) {
    echo '<div class="form-group">
            <label for="default" class="col-sm-2 control-label">Class</label>
            <div class="col-sm-10">' . htmlentities($studentDetails->ClassName) . ' (' . htmlentities($studentDetails->Section) . ')</div>
          </div>
          <div class="form-group">
            <label for="default" class="col-sm-2 control-label">Full Name</label>
            <div class="col-sm-10">' . htmlentities($studentDetails->StudentName) . '</div>
          </div>';
}

// Fetch results based on the selected exam category
$sql = "SELECT tblsubjects.SubjectName, tblresult.marks, tblresult.id as resultid 
        FROM tblresult 
        JOIN tblsubjects ON tblsubjects.id = tblresult.SubjectId 
        WHERE tblresult.StudentId = :stid AND tblresult.exam_category = :exam_category";
$query = $dbh->prepare($sql);
$query->bindParam(':stid', $stid, PDO::PARAM_STR);
$query->bindParam(':exam_category', $exam_category, PDO::PARAM_STR);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if($query->rowCount() > 0) {
    foreach($results as $result) {
        echo '<div class="form-group">
                <label for="default" class="col-sm-2 control-label">' . htmlentities($result->SubjectName) . '</label>
                <div class="col-sm-10">
                    <input type="hidden" name="id[]" value="' . htmlentities($result->resultid) . '">
                    <input type="text" name="marks[]" class="form-control" value="' . htmlentities($result->marks) . '" />
                </div>
              </div>';
    }
} else {
    echo '<div class="alert alert-warning left-icon-alert" role="alert">
            <strong>Notice!</strong> No results available for this exam category.
          </div>';
}
?>