<?php
include('includes/config.php');

if(isset($_POST['exam_category_id'])) {
    $exam_category_id = $_POST['exam_category_id'];

    // Check that the provided ID is numeric to prevent SQL injection
    if(is_numeric($exam_category_id)) {
        $sql = "SELECT exam_date FROM tblexamcategories WHERE id = :exam_category_id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':exam_category_id', $exam_category_id, PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
        
        if($result) {
            echo htmlentities($result->exam_date);
        } else {
            echo "No date found";
        }
    } else {
        echo "Invalid ID";
    }
} else {
    echo "No category ID provided";
}
?>