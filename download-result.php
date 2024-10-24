<?php
namespace Dompdf;
require_once 'dompdf/autoload.inc.php';
session_start();
ob_start();
require_once('includes/configpdo.php');
error_reporting(E_ALL & ~E_WARNING & ~E_DEPRECATED); // Suppress warnings and deprecated errors

if (!isset($_SESSION['rollid']) || !isset($_SESSION['classid'])) {
    echo "Roll ID or Class ID not set!";
    exit();
}

$rollid = $_SESSION['rollid'];
$classid = $_SESSION['classid'];

// Fetch student details
$qery = "SELECT tblstudents.StudentName, tblstudents.RollId, tblclasses.ClassName, tblclasses.Section 
         FROM tblstudents 
         JOIN tblclasses ON tblclasses.id=tblstudents.ClassId 
         WHERE tblstudents.RollId=? AND tblstudents.ClassId=?";
$stmt21 = $mysqli->prepare($qery);
$stmt21->bind_param("ss", $rollid, $classid);
$stmt21->execute();
$res1 = $stmt21->get_result();

$studentInfo = $res1->fetch_object();
?>

<html>

<head>
    <style>
    body {
        padding: 4px;
        text-align: center;
    }

    table {
        width: 100%;
        margin: 10px auto;
        table-layout: auto;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 10px;
        border: 1px solid black;
        text-align: center;
    }
    </style>
</head>

<body>
    <div>
        <h1>Student Result</h1>
        <p><b>Student Name:</b> <?php echo htmlentities($studentInfo->StudentName); ?></p>
        <p><b>Roll ID:</b> <?php echo htmlentities($studentInfo->RollId); ?></p>
        <p><b>Class:</b> <?php echo htmlentities($studentInfo->ClassName); ?>
            (<?php echo htmlentities($studentInfo->Section); ?>)</p>
    </div>

    <div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Marks</th>
                </tr>
            </thead>
            <tbody>
                <?php
        // Fetch results
        $query = "SELECT tblsubjects.SubjectName, t.marks 
                  FROM (SELECT sts.RollId, tr.marks, tr.SubjectId 
                        FROM tblstudents AS sts 
                        JOIN tblresult AS tr ON tr.StudentId=sts.StudentId 
                        WHERE sts.RollId=? AND sts.ClassId=?) AS t 
                  JOIN tblsubjects ON tblsubjects.id=t.SubjectId";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("ss", $rollid, $classid);
        $stmt->execute();
        $res = $stmt->get_result();

        $cnt = 1;
        $totlcount = 0;

        while ($row = $res->fetch_object()) {
            $marks = (float)$row->marks; // Ensure that marks are treated as a float
        ?>
                <tr>
                    <td><?php echo htmlentities($cnt); ?></td>
                    <td><?php echo htmlentities($row->SubjectName); ?></td>
                    <td><?php echo htmlentities($marks); ?></td>
                </tr>
                <?php
            $totlcount += $marks;
            $cnt++;
        }
        $outof = ($cnt - 1) * 100;
        $percentage = round(($totlcount * 100) / $outof, 2);
        ?>
                <tr>
                    <th colspan="2">Total Marks</th>
                    <td><?php echo htmlentities($totlcount); ?> out of <?php echo htmlentities($outof); ?></td>
                </tr>
                <tr>
                    <th colspan="2">Percentage</th>
                    <td><?php echo htmlentities($percentage); ?>%</td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>

<?php
$html = ob_get_clean();
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("result.pdf", array("Attachment" => false));
?>