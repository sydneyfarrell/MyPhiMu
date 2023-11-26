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

$columns = array('lname','pc', 'fines', 'total', 'date');
$column = isset($_GET['column']) && in_array($_GET['column'], $columns) ? $_GET['column'] : $columns[0];
$sort_order = isset($_GET['order']) && strtolower($_GET['order']) == 'desc' ? 'DESC' : 'ASC';

$query = ('SELECT Member.memberID, fname, lname, pc, duesID,
COALESCE(SUM(fineAmount), 0) AS fines, 
COALESCE(SUM(dueAmount), 0) + COALESCE(SUM(fineAmount), 0) AS total, 
MAX(Dues.date) AS date FROM Dues INNER JOIN Member ON Member.memberID = Dues.memberID 
LEFT JOIN Fines ON Fines.fineID = Dues.fineID WHERE MONTH(Dues.date) = MONTH(NOW())
GROUP BY Member.memberID, fname, lname, pc ORDER BY ' . $column . ' ' . $sort_order);

$stmt2 = $mysqli -> prepare($query);
$stmt2 -> execute();

$up_or_down = str_replace(array('ASC','DESC'), array('up','down'), $sort_order); 
$asc_or_desc = $sort_order == 'ASC' ? 'desc' : 'asc';
$highlight = ' class="highlight"';
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <title>MyPhiMu - Member Dues</title>
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
.color {
  background-color: yellow;
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
            Chapter Data
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="viewMemberPoints.php">Member Point Submission</a></li>
            <li><a class="dropdown-item" href="viewExcuseForms.php">Submitted Excuse Forms</a></li>
            <?php
            $stmt = $mysqli -> prepare('select MAX(Dues.date) AS date from Dues');
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
    data-bs-target='#exampleModal'>Send Monthly Dues to Chapter</button>
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
      <li class="nav-item">
          <a class="nav-link" href="form.php">Points Form</a>
      </li>     
      </ul>
    </div>
  </div>
</nav> 
<body>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Send Monthly Dues to Chapter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <form method="POST" action="viewDues.php">
        <?php
            echo "<div class='row'>";
            echo "<center>";
            echo "<table class = table table-borderless>";
            echo "<tr>";
            echo "<th>Date Due</th>";
            echo "<th>Amount</th>";
            echo "<th>Administer Dues</th>";
            echo "</tr>";
            echo "<tr>";
            echo "<td class='text-center'><input type='date' id = 'dueDate' class='form-control'
            name='dueDate' required></td>";
            echo "<td class='text-center'><input type='number' id = 'amount' class='form-control'
            placeholder='Write Amount' name='amount' required></td>";
                ?>
                <td class="text-center">
                  <button type="submit" name = "submit" class="btn btn-primary" style="padding: 10px;">Send</button>
                  </td>
                </tr>
              </table>
            </center>
          </div>
        </form>
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
      <?php
      $stmt = $mysqli -> prepare('select MAX(Dues.date) AS date from Dues');
      $stmt -> execute();
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $date = $row['date'];
      $dateTime = new DateTime($date);
      $formattedDate = $dateTime->format("F");

      echo "<h4>".$formattedDate." Chapter Dues"."</h4>";
      }
    ?>
    <p></p>
    <table class="main-table">
	<tr>
		<th></th>
		<th><a href="viewDues.php?column=lname&order=<?php echo $asc_or_desc; ?>">Name<i class="fas fa-sort<?php echo $column == 'lname' ? '-' . $up_or_down : ''; ?>"></i></a></th>
		<th><a href="viewDues.php?column=pc&order=<?php echo $asc_or_desc; ?>">Pledge Class<i class="fas fa-sort<?php echo $column == 'pc' ? '-' . $up_or_down : ''; ?>"></i></a></th>
		<th><a href="viewDues.php?column=fines&order=<?php echo $asc_or_desc; ?>">Fine Amount<i class="fas fa-sort<?php echo $column == 'fines' ? '-' . $up_or_down : ''; ?>"></i></a></th>
    <th><a href="viewDues.php?column=total&order=<?php echo $asc_or_desc; ?>">Total Amount<i class="fas fa-sort<?php echo $column == 'total' ? '-' . $up_or_down : ''; ?>"></i></a></th>
    <th><a href="viewDues.php?column=date&order=<?php echo $asc_or_desc; ?>">Date Due<i class="fas fa-sort<?php echo $column == 'date' ? '-' . $up_or_down : ''; ?>"></i></a></th>
    <th></th>

    
	</tr>
	<?php while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)): ?>
	<tr>
  <td<?php echo $column == 'lname' ? $highlight : ''; ?>><?php echo $row["fname"]." ".$row['lname']; ?></td>
  <td<?php echo $column == 'pc' ? $highlight : ''; ?>><?php echo $row['pc']; ?></td>
  <td<?php echo $column == 'fines' ? $highlight : ''; ?>><?php echo "$".$row['fines']; ?></td>
  <td<?php echo $column == 'total' ? $highlight : ''; ?>><?php echo "$".$row['total']; ?></td>
  <td<?php echo $column == 'date' ? $highlight : ''; ?>><?php echo $row['date']; ?></td>
</tr>
	<?php endwhile; ?>
</table>
		</center>
		</div>
    </body>
    </html>

<?php
Database::dbDisconnect($mysqli);
?>