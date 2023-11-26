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

$stmt2 = $mysqli->prepare("select memberID, fname, lname, pc, image, email, phone, city, state from Member where memberID = ?");
$stmt2->execute([$memberID]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>MyPhiMu</title>
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
        <a class='nav-link active' aria-current='page' href='member.php'>Home</a>
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


<div id="myModal" class="modal-fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <?php
        if ($stmt2->rowCount() > 0) {
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            echo "<h3 class='col-12 modal-title text-center'>"."Hello, ".$row["fname"]." ".$row['lname']."!"."<br />"."<img src =".$row["image"]." width='200'>"."</h3>";
      ?>
      </div>
      <div class="modal-body">
	  	<h5 class = "text-center">Pledge Class: <?php echo $row["pc"]?></h5>
		<h5 class = "text-center">Email: <?php echo $row["email"]?></h5> 
		<h5 class = "text-center">Phone number: <?php echo $row["phone"]?></h5>
		<h5 class = "text-center">Hometown: <?php echo $row["city"].", ".$row["state"]?></h5>
        </div>
    </div>

  </div>
</div>
</body>
</html>

<?php
        }
Database::dbDisconnect($mysqli);
?>