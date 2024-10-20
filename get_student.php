<?php
include('includes/config.php');

// Fetch students based on class and exam category
if (!empty($_POST["classid"]) && !empty($_POST["exam_category"])) {
    $cid = intval($_POST['classid']);
    $exam_category = $_POST['exam_category'];

    if (!is_numeric($cid)) {
        echo htmlentities("Invalid Class"); 
        exit;
    } else {
        $stmt = $dbh->prepare("SELECT StudentName, StudentId FROM tblstudents WHERE ClassId= :id ORDER BY StudentName");
        $stmt->execute(array(':id' => $cid));
        ?>
<option value="">Select Student</option>
<?php
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            ?>
<option value="<?php echo htmlentities($row['StudentId']); ?>">
    <?php echo htmlentities($row['StudentName']); ?>
</option>
<?php
        }
    }
}

// Fetch subjects for the class
if (!empty($_POST["classid1"])) {
    $cid1 = intval($_POST['classid1']);
    if (!is_numeric($cid1)) {
        echo htmlentities("Invalid Class"); 
        exit;
    } else {
        $status = 0;
        $stmt = $dbh->prepare("SELECT tblsubjects.SubjectName, tblsubjects.id FROM tblsubjectcombination 
                               JOIN tblsubjects ON tblsubjects.id = tblsubjectcombination.SubjectId 
                               WHERE tblsubjectcombination.ClassId = :cid AND tblsubjectcombination.status != :stts 
                               ORDER BY tblsubjects.SubjectName");
        $stmt->execute(array(':cid' => $cid1, ':stts' => $status));

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
<p><?php echo htmlentities($row['SubjectName']); ?>
    <input type="text" name="marks[]" value="" class="form-control" required="" placeholder="Enter marks out of 100"
        autocomplete="off">
</p>
<?php
        }
    }
}

// Check if the result is already declared for the student and class
if (!empty($_POST["studclass"])) {
    $id = $_POST['studclass'];
    $dta = explode("$", $id);
    $classid = $dta[0];
    $studentid = $dta[1];

    $query = $dbh->prepare("SELECT StudentId, ClassId FROM tblresult WHERE StudentId = :studentid AND ClassId = :classid");
    $query->bindParam(':studentid', $studentid, PDO::PARAM_STR);
    $query->bindParam(':classid', $classid, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if ($query->rowCount() > 0) { ?>
<p><span style='color:red'>Result Already Declared.</span></p>
<script>
$('#submit').prop('disabled', true);
</script>
<?php
    }
}
?>