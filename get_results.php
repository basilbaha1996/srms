<?php
include('includes/config.php');

if(isset($_POST['exam_category']) && isset($_POST['student_id'])) {
    $exam_category = intval($_POST['exam_category']);
    $student_id = intval($_POST['student_id']);

    $sql = "SELECT tblsubjects.SubjectName, tblresult.marks, tblresult.id as resultid 
            FROM tblresult 
            JOIN tblsubjects ON tblsubjects.id = tblresult.SubjectId 
            WHERE tblresult.StudentId = :student_id AND tblresult.exam_category = :exam_category";
    
    $query = $dbh->prepare($sql);
    $query->bindParam(':student_id', $student_id, PDO::PARAM_STR);
    $query->bindParam(':exam_category', $exam_category, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if($query->rowCount() > 0) {
        echo '<form class="form-horizontal" method="post">';
        foreach($results as $result) {
            echo '<div class="form-group">';
            echo '<label class="col-sm-2 control-label">'.htmlentities($result->SubjectName).'</label>';
            echo '<div class="col-sm-10">';
            echo '<input type="hidden" name="id[]" value="'.htmlentities($result->resultid).'">';
            echo '<input type="text" name="marks[]" class="form-control" value="'.htmlentities($result->marks).'" required>';
            echo '</div></div>';
        }
        echo '<div class="form-group btn-section">';
        echo '<button type="submit" name="submit" class="btn btn-primary">Update Results</button>';
        echo '<button type="submit" name="delete" class="btn btn-danger">Delete Results</button>';
        echo '</div></form>';
    } else {
        echo '<div class="alert alert-warning">No results found for the selected exam category.</div>';
    }
}
?>