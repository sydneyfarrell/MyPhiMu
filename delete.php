<?php

session_start();

if (!isset($_SESSION['memberID'])) {
    header("Location: login.php");
    exit();
}

$memberID = $_SESSION['memberID'];

require_once("database.php");

$mysqli = Database::dbConnect();
$mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $mysqli->prepare("SELECT * FROM Member WHERE memberID = ?");
$stmt->execute([$memberID]);

if ($stmt->rowCount() == 0) {
    header("Location: login.php");
    exit();
}

function goHome($memberID, $mysqli) {
    $stmt = $mysqli->prepare("SELECT * FROM Exec_Member WHERE memberID = ?");
    $stmt->execute([$memberID]);

    if ($stmt->rowCount() > 0) {
        return "admin.php";
    } else {
        return "member.php";
    }
}

$query = ("SELECT fname, lname, pointDesc, Point_Type.typeDesc AS typeDesc, 
COUNT(*) AS pointCount, (SELECT COUNT(*) FROM Points WHERE Points.memberID = ?) AS total
from Points INNER JOIN Member ON Member.memberID = Points.memberID
INNER JOIN Point_Type ON Point_Type.typeID = Points.typeID WHERE Points.memberID = ?
GROUP BY fname, lname, pointDesc, Point_Type.typeDesc");

$stmt = $mysqli -> prepare($query);
$stmt->execute([$memberID, $memberID]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>MyPhiMu - Check Points</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="css/style.css">

</head>

<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
      <li class="nav-item">
      <a class='nav-link active' aria-current='page' href='<?php echo goHome($memberID, $mysqli); ?>'>Home</a>
        </li>
        </li>
		<li class="nav-item">
            <a class="nav-link" href="calendar.php">Calendar</a>
          </li>
		  <li class="nav-item">
            <a class="nav-link" href="excuses.php">Submit Excuse Form</a>
          </li>
		  <li class="nav-item">
            <a class="nav-link" href="dues.php">Check Dues</a>
          </li>
          <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Points Links
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="form.php">Points Form</a></li>
            <li><a class="dropdown-item" href="checkPoints.php">View Points Earned</a></li>
            </ul>
        </li>
        
      </ul>
	  <form class="d-flex">
	  <a class="btn" href="logout.php">Logout</a>   
	   </form>
    </div>
  </div>
</nav>
<br/>
<br/>
        
<!-- Modal -->
<div id="myModal" class="modal-fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
      <?php
        if ($stmt->rowCount() > 0) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<h5 class='modal-title'>" . "Total Number of Points: " . $rows[0]["total"] . "/5"."</h5>";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php
            echo "<div class='row'>";
            echo "<center>";
            echo "<table class = table table-borderless>";
            echo "<tr>";
            echo "<th>Point Category</th>";
            echo "<th>Description</th>";
            echo "<th>Total</th>";
            echo "</tr>";
            foreach ($rows as $row) {
                echo "<tr>";
                echo "<td>".$row["typeDesc"]."</td>";
                echo "<td>".$row["pointDesc"]."</td>";
                echo "<td>".$row["pointCount"]."</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</center>";
            echo "</div>";
        }
        ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
</body>
</html>

<?php

Database::dbDisconnect($mysqli);
?>