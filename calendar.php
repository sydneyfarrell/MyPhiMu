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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <title>MyPhiMu Calendar</title>
    <style>
        .calendar-table {
            width: 100%;
            border-collapse: collapse;
        }
        .calendar-table th, td {
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f2f2f2;
        }
        .calendar-table td {
            height: 80px;
            position: relative;
            width: 50px;
            color: black;
        }
        .calendar-table th {
            text-align: left;
            background-color: #54585d;
            color: white;
        }
        body {
            padding: 0;
            margin: 15px;
        }

        h4 {
            margin-top: 6px; 
            text-align: center;
            font-weight: bold;
        }

        .calendar {
            text-align: center;
            margin-bottom: 10px;
            margin-top: 6px;
        }
    
        .calendar a {
            text-decoration: none;
            padding: 5px;
            margin: 0 6px;
            background-color: #54585d;
            color: white;
            border-radius: 5px;
        }

        ul.navbar-nav {
            margin: 0;
        }
        .day-number {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 11px;
            color: #54585d;
        }
        .center {
            position: absolute;
            top: 50%;
            width: 100%;
            text-align: center;
            font-size: 12px;
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

<?php

if (isset($_GET['month'])) {
    $month = $_GET['month'];
} else {
    $month = date('m');
}

if (isset($_GET['year'])) {
    $year = $_GET['year'];
} else {
    $year = date('Y');
}

$query = 'SELECT * FROM Event WHERE YEAR(date) = :year AND MONTH(date) = :month';
$stmt = $mysqli->prepare($query);
$stmt->bindParam(':year', $year, PDO::PARAM_INT);
$stmt->bindParam(':month', $month, PDO::PARAM_INT);
$stmt->execute();

$events = array();

if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $events[$row['date']][] = $row['eventDesc'];
    }
}

echo '<h4>'. 
'<form class="d-flex" action="" method= "POST">'.
'</form>'.'<div class="calendar">' .
'<a href="?month=' . ($month - 1) . '&year=' . $year . '"><</a>' 
. date('F Y', strtotime("$year-$month-01")) . 
'<a href="?month=' . ($month + 1) . '&year=' . $year . '">></a>' . '</div>' .'</h4>';
echo '<table class = "calendar-table">';
echo '<tr>';
echo '<th>Sun</th>';
echo '<th>Mon</th>';
echo '<th>Tue</th>';
echo '<th>Wed</th>';
echo '<th>Thu</th>';
echo '<th>Fri</th>';
echo '<th>Sat</th>';
echo '</tr>';

$firstDayOfMonth = new DateTime("$year-$month-01");
$lastDayOfMonth = new DateTime("last day of $year-$month");

$currentDay = clone $firstDayOfMonth;
$currentDayTimestamp = $firstDayOfMonth->getTimestamp();

echo '<tr>';
for ($i = 0; $i < $currentDay->format('w'); $i++) {
    echo '<td></td>';
}

while ($currentDay <= $lastDayOfMonth) {
    echo '<td>';
    $currentDayFormatted = $currentDay->format('Y-m-d');
    echo '<span class="day-number">' . $currentDay->format('j') . '</span><br>';

    if (isset($events[$currentDayFormatted])) {
        foreach ($events[$currentDayFormatted] as $event) {
            echo '<div style="background-color: #FF69B4; font-size: 12px;
            padding: 7px; text-align: center; margin-top: 5px;
            display: inline-block; color= "black"; border-radius: 10px;>
            ' . $event . '</div>';
        }
    }

    echo '</td>';

    $currentDay->modify('+1 day');

    if ($currentDay->format('w') == 0 && $currentDay <= $lastDayOfMonth) {
        echo '</tr><tr>';
    }
}

echo '</tr>';
echo '</table>';

Database::dbDisconnect($mysqli);
?>
</body>
</html>
