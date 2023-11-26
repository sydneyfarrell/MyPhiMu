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

$selectedMonth = date('m');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['selectedMonth']) && is_numeric($_POST['selectedMonth'])) {
      $selectedMonth = (int)$_POST['selectedMonth'];

      if ($_POST['check'] === 'prev') {
          $selectedMonth = $selectedMonth - 1;
      } elseif ($_POST['check'] === 'next') {
          $selectedMonth = $selectedMonth + 1;
      }
  } else {
      $selectedMonth = date('m');
  } if (isset($_POST['submit']) && $_POST['submit'] === 'pay') {
    $insertStmt = $mysqli->prepare("UPDATE Dues SET isPaid = 'Y' WHERE Dues.memberID = ? AND MONTH(Dues.date) = ?");
    $insertStmt->execute([$memberID, $selectedMonth]);
    header("Location: dues.php");
    exit();
  }
}

$stmtPrev = $mysqli->prepare("SELECT COUNT(*) FROM Dues WHERE MONTH(Dues.date) = ? AND Dues.memberID = ?");
$stmtPrev->execute([$selectedMonth - 1, $memberID]);
$duesPrev = $stmtPrev->fetchColumn();

$stmtNext = $mysqli->prepare("SELECT COUNT(*) FROM Dues WHERE MONTH(Dues.date) = ? AND Dues.memberID = ?");
$stmtNext->execute([$selectedMonth + 1, $memberID]);
$duesNext = $stmtNext->fetchColumn();

function goHome($memberID, $mysqli) {
  $stmt = $mysqli->prepare("SELECT * FROM Exec_Member WHERE memberID = ?");
  $stmt->execute([$memberID]);

  if ($stmt->rowCount() > 0) {
      return "admin.php";
  } else {
      return "member.php";
  }
}

$query = ('SELECT fname, lname, pc, duesID, GROUP_CONCAT(Event.eventDesc SEPARATOR ", ") AS eventDesc,
COALESCE(SUM(dueAmount), 0) AS monthly, COALESCE(SUM(fineAmount), 0) AS fines, 
COALESCE(SUM(dueAmount), 0) + COALESCE(SUM(fineAmount), 0) AS total, isPaid,
MAX(Dues.date) AS date FROM Dues INNER JOIN Member ON Member.memberID = Dues.memberID 
LEFT JOIN Fines ON Fines.fineID = Dues.fineID LEFT JOIN Event ON Event.eventID = Fines.eventID
WHERE MONTH(Dues.date) = ? AND Dues.memberID = ? GROUP BY Dues.memberID'); 

$stmt2 = $mysqli -> prepare($query);
$stmt2->execute([$selectedMonth, $memberID]);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>MyPhiMu - Check Dues</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<style>
.btn-primary {
  background-color: #808080; 
  border: none;
  color: white;
  padding: 10px 24px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 17px;
  border-color: #FF69B4;
}
.btn-primary:hover {
  background-color: #FF69B4; 
  color: white;
  border-color: #FF69B4;
}
.btn:disabled {
    background-color: black;
    border-color: black;
    color: white;
}

</style>

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

<form action="" method="POST">
  <div id="myModal" class="modal-fade" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content" style="height:370px; width: 520px;">
      <div class="modal-header">
      <?php
      if ($stmt2->rowCount() > 0) {
      $row = $stmt2->fetch(PDO::FETCH_ASSOC);

      $date = $row['date'];
      $dateTime = new DateTime($date);
      $formattedDate = $dateTime->format("F");

      if ($row["isPaid"] == 'Y') {
        $buttonStyle = 'btn-success';
        $buttonText = 'PAID';
        //$isButtonDisabled = true;
    } elseif ($dateTime > new DateTime()) {
        $buttonStyle = 'btn-secondary';
        $buttonText = 'NOT DUE';
        $lateFee = 0;
        $isButtonDisabled = true;
    } elseif ($dateTime == new DateTime()) {
        $buttonStyle = 'btn-danger';
        $buttonText = 'DUE';
        $lateFee = 0;
        $isButtonDisabled = false;
    } else {
        $buttonStyle = 'btn-danger';
        $buttonText = 'UNPAID';
        $lateFee = 10; 
        $isButtonDisabled = false;
    }
      $total = $row["total"] + $lateFee;
        
      echo "<h3 class='col-12 modal-title text-center' style='font-weight: bold;'>
      " . $formattedDate . " Dues: " . "$" . $total . "</h3>";
      ?>
      </div>
      <div class="modal-body">
      <h5 class="text-center">Event(s) Missed: <?php echo ($row["eventDesc"] === NULL ? 'None' : $row["eventDesc"]); ?></h5>
      <h5 class="text-center">Fine Amount: <?php echo "$" . $row["fines"] ?></h5>
      <h5 class="text-center">Monthly Amount: <?php echo "$" . $row["monthly"] ?></h5>
      <h5 class="text-center">Due Date: <?php echo $row["date"] ?></h5>
      <?php if ($lateFee > 0) { ?>
        <h5 class="text-center">Late Fee: <?php echo "$" . $lateFee ?></h5>
      <?php } ?>
      </div>
      <div class="modal-footer">
      <?php echo "<button type='submit' value='pay' name='submit' class='btn $buttonStyle'" . ($isButtonDisabled ? ' disabled' : '') . " data-bs-dismiss='modal'>$buttonText</button>";
      ?>  </div>
      </div>
      </div>
  </div>
  <h4 class="text-center"><?php
    echo "<input type='hidden' name='currentMonth' value='" . $selectedMonth . "'>";
    echo "<button type='submit' class='btn btn-primary' name='check' value='prev'" . ($duesPrev == 0 ? ' disabled' : '') . ">Previous Month</button>";
    echo "<span style='margin: 0 10px;'></span>";
    echo "<button type='submit' class='btn btn-primary' name='check' value='next'" . ($duesNext == 0 ? ' disabled' : '') . ">Next Month</button>";
    echo "<input type='hidden' name='selectedMonth' value='" . $selectedMonth . "'>";
    ?>
  </h4>
</form>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
      }
Database::dbDisconnect($mysqli);
?>