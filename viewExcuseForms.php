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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $memberID = $_POST['memberID'];
  $eventID = $_POST['eventID'];

  $query = ("SELECT fineID FROM Fines WHERE eventID = ?");
  $stmt = $mysqli->prepare($query);
  $stmt->execute([$eventID]);

if ($stmt) {
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($result) {
    $fineID = $result['fineID'];

  if (!empty($fineID) && is_numeric($fineID)) {
      $query2 = "INSERT INTO Dues (memberID, fineID, date) VALUES (?, ?, NOW())";
      $stmt2 = $mysqli->prepare($query2);
      $stmt2->execute([$memberID, $fineID]);   
    }
  }
}
}

$query3 = "SELECT DISTINCT memberID, fineID FROM Dues";
$stmt3 = $mysqli->prepare($query3);
$stmt3->execute();

if ($stmt3) {
  while ($duesRow = $stmt3->fetch(PDO::FETCH_ASSOC)) {
  $membersWithFines[$duesRow['memberID']][] = $duesRow['fineID'];
  }
}  

$columns = array('lname','pc', 'event', 'reason', 'date');
$column = isset($_GET['column']) && in_array($_GET['column'], $columns) ? $_GET['column'] : $columns[0];
$sort = isset($_GET['order']) && strtolower($_GET['order']) == 'desc' ? 'DESC' : 'ASC';

$query = ('select excuseID, Member.fname AS fname, Member.lname AS lname, Member.pc AS pc, 
Event.eventDesc AS event, Excuse_Form.excuseDesc AS reason, Excuse_Form.date AS date, 
Event.date AS eventDate from Excuse_Form INNER JOIN Member on 
Member.memberID = Excuse_Form.memberID INNER JOIN Event on Event.eventID = Excuse_Form.eventID 
ORDER BY ' .  $column . ' ' . $sort);

$stmt2 = $mysqli -> prepare($query);
$stmt2 -> execute();

if ($stmt2) {
    $up_or_down = str_replace(array('ASC','DESC'), array('up','down'), $sort); 
    $asc_or_desc = $sort == 'ASC' ? 'desc' : 'asc';
    $highlight = ' class="highlight"';
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <title>MyPhiMu - Excuse Forms</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
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
  font-size: 15px;
}

.btn-primary:hover {
  background-color: #FF69B4; 
  color: white;
}

.main-table {
    border-collapse: collapse;
    width: 1200px;
}

.main-table th {
    background-color: #54585d;
    border: 1px solid #54585d;
}

.main-table th:hover {
    background-color: #64686e;
}

.main-table th a {
	display: block;
	text-decoration:none;
	padding: 10px;
	color: #ffffff;
	font-weight: bold;
	font-size: 15px;
}

.main-table th a i {
	margin-left: 5px;
	color: rgba(255,255,255,0.4);
}

.main-table th a:hover {
    color: #FFFF00;
}

.main-table td {
    padding: 10px;
    color: #636363;
    border: 1px solid #dddfe1;
}

.main-table tr {
    background-color: #ffffff;
}

.main-table tr .highlight {
	background-color: #f9fafb;
}
</style>
</head>


<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="phimulogo.png"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="admin.php">Admin Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">Logout</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Chapter data
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="viewMemberPoints.php">Member point submission</a></li>
            <li><a class="dropdown-item" href="viewExcuseForms.php">Submitted Excuse Forms</a></li>
            <?php
            $stmt = $mysqli -> prepare('select Dues.date AS date 
            from Dues where MONTH(Dues.date) = MONTH(NOW())');
            $stmt -> execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $date = $row['date'];
            $dateTime = new DateTime($date);
            $formattedDate = $dateTime->format("F");
            }
            ?>
            <li><a class="dropdown-item" href="viewDues.php"><?php echo "Chapter Dues - ".$formattedDate?></a></li>
          </ul>
        </li>
        
      </ul>
      <form class="d-flex">
    <button type='button' class='btn btn-primary' data-bs-toggle='modal'
    data-bs-target='#exampleModal'>View Members without an Excuse</button>
      </form>
    </div>
  </div>
</nav>


<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
      <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="member.php">Home</a>
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
    </div>
  </div>
</nav> 
<body>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Members without an Excuse</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php
        $query = "SELECT Member.memberID AS memberID, fname, lname, pc, 
                  Event.eventID AS eventID, Event.eventDesc, Event.date 
                  FROM Member 
                  CROSS JOIN Event 
                  LEFT JOIN Excuse_Form 
                  ON Member.memberID = Excuse_Form.memberID 
                  AND Event.eventID = Excuse_Form.eventID 
                  WHERE Event.date < NOW()
                  AND MONTH(Event.date) = MONTH(NOW()) 
                  AND Excuse_Form.excuseID IS NULL";

        $stmt = $mysqli->prepare($query);
        $stmt->execute();

        if ($stmt) {
          echo "<div class='row'>";
          echo "<center>";
          echo "<table class='table table-borderless'>";
          echo "<tr>";
          echo "<th>Name</th>";
          echo "<th>Pledge Class</th>";
          echo "<th>Event Missed</th>";
          echo "<th>Administer Fine</th>";
          echo "</tr>";
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!isset($membersWithFines[$row['memberID']]) || !in_array($row['eventID'], $membersWithFines[$row['memberID']])) {
              ?>
              <form method="POST" action="">
                <input type="hidden" name="memberID" value="<?php echo $row['memberID']; ?>">
                <input type="hidden" name="eventID" value="<?php echo $row['eventID']; ?>">

                <?php
                $date = $row['date'];
                $dateTime = new DateTime($date);
                $formattedDate = $dateTime->format("n/j");

                echo "<tr>";
                echo "<td>" . $row["fname"] . " " . $row['lname'] . "</td>";
                echo "<td>" . $row["pc"] . "</td>";
                echo "<td>" . $row['eventDesc'] . " " . $formattedDate . "</td>";
                ?>
                <td class="text-center">
                  <button type="submit" class="btn btn-primary" style="padding: 10px;">Fine</button>
                </td>
              </form>
              <?php
              echo "</tr>";
            }
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


<body>
<div class='row'>
		<center>
      <p></p>
    <h4>Submitted Excuse Forms</h4>
    <p></p>
    <table class="main-table">
	<tr>
		<th></th>
		<th><a href="viewExcuseForms.php?column=lname&order=<?php echo $asc_or_desc; ?>">Name<i class="fas fa-sort<?php echo $column == 'lname' ? '-' . $up_or_down : ''; ?>"></i></a></th>
		<th><a href="viewExcuseForms.php?column=pc&order=<?php echo $asc_or_desc; ?>">Pledge Class<i class="fas fa-sort<?php echo $column == 'pc' ? '-' . $up_or_down : ''; ?>"></i></a></th>
		<th><a href="viewExcuseForms.php?column=event&order=<?php echo $asc_or_desc; ?>">Event<i class="fas fa-sort<?php echo $column == 'event' ? '-' . $up_or_down : ''; ?>"></i></a></th>
		<th><a href="viewExcuseForms.php?column=reason&order=<?php echo $asc_or_desc; ?>">Reason<i class="fas fa-sort<?php echo $column == 'reason' ? '-' . $up_or_down : ''; ?>"></i></a></th>
    <th><a href="viewExcuseForms.php?column=date&order=<?php echo $asc_or_desc; ?>">Date Submitted<i class="fas fa-sort<?php echo $column == 'date' ? '-' . $up_or_down : ''; ?>"></i></a></th>
    <th></th>

    
	</tr>
	<?php while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)): ?>
		<?php
    $excuseDate = $row['date'];
    $eventDate = $row['eventDate'];

    $late = ($excuseDate > $eventDate) ? 'background-color: #FF7F7F;' : '';

    echo "<tr style='$late'>";
		echo "<td><a href='deleteExcuse.php?id=".urlencode($row['excuseID'])."' onclick='return confirm(\"Are you sure?\");'>&#10060</a></td>"; ?>
    <td style='<?php echo $late?>'<?php echo $column == 'lname' ? $highlight : ''; ?>><?php echo $row["fname"]." ".$row['lname']; ?></td>
    <td style='<?php echo $late?>'<?php echo $column == 'pc' ? $highlight : ''; ?>><?php echo $row['pc']; ?></td>
		<td style='<?php echo $late?>'<?php echo $column == 'event' ? $highlight : ''; ?>><?php echo $row['event']; ?></td>
		<td style='<?php echo $late?>'<?php echo $column == 'reason' ? $highlight : ''; ?>><?php echo $row['reason']; ?></td>
    <td style='<?php echo $late?>'<?php echo $column == 'date' ? $highlight : ''; ?>><?php echo $row['date']; ?></td>
	</tr> <?php
endwhile; ?>
</table>
		</center>
		</div>
    </body>
    </html>

<?php
Database::dbDisconnect($mysqli);
?>