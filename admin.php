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

$columns = array('fname', 'lname', 'pc', 'email', 'phone', 'city', 'state');
$column = isset($_GET['column']) && in_array($_GET['column'], $columns) ? $_GET['column'] : $columns[0];
$sort = isset($_GET['order']) && strtolower($_GET['order']) == 'desc' ? 'DESC' : 'ASC';

$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';
$searchQuery = '%' . $searchTerm . '%';

$query = 'SELECT * FROM Member WHERE fname LIKE ? OR lname LIKE ? OR pc LIKE ? OR email LIKE ? OR phone LIKE ? OR city LIKE ? OR state LIKE ? ORDER BY ' . $column . ' ' . $sort;
$stmt2 = $mysqli->prepare($query);
$stmt2->execute([$searchQuery, $searchQuery, $searchQuery, $searchQuery, $searchQuery, $searchQuery, $searchQuery]);

if ($stmt2) {
    $up_or_down = str_replace(array('ASC', 'DESC'), array('up', 'down'), $sort);
    $asc_or_desc = $sort == 'ASC' ? 'desc' : 'asc';
    $highlight = ' class="highlight"';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>MyPhiMu Admin</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="css/style.css">

<style>
table {
	border-collapse: collapse;
	width: 1200px;
}
th {
	background-color: #54585d;
	border: 1px solid #54585d;
}
th:hover {
	background-color: #64686e;
}
th a {
	display: block;
	text-decoration:none;
	padding: 10px;
	color: #ffffff;
	font-weight: bold;
	font-size: 15px;
}
th a i {
	margin-left: 5px;
	color: rgba(255,255,255,0.4);
}
th a:hover {
  color: #FFFF00;
}
td {
	padding: 10px;
	color: #636363;
	border: 1px solid #dddfe1;
}
tr {
	background-color: #ffffff;
}
tr .highlight {
	background-color: #f9fafb;
}
</style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="phimulogo.png"></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
        <a class='nav-link active' aria-current='page' href='admin.php'>Admin Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">Logout</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Chapter Data
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
        <form class="d-flex" method="post" action="admin.php">
            <input class="search" type="search" name="search" placeholder="Search" aria-label="Search"
                value="<?php echo $searchTerm; ?>">
                <button class="btn" type="submit" style="border-color: hotpink; color: hotpink;" 
                onclick="this.style.backgroundColor='hotpink'; this.style.color='white'">Search</button>
        </form>
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
    </div>
  </div>
</nav>

<div class='row'>
		<center>
      <p></p>
    <h4>Member Data</h4>
    <p></p>
    <table>
	<tr>
		<th></th>
		<th><a href="admin.php?column=lname&order=<?php echo $asc_or_desc; ?>">Name<i class="fas fa-sort<?php echo $column == 'lname' ? '-' . $up_or_down : ''; ?>"></i></a></th>
		<th><a href="admin.php?column=pc&order=<?php echo $asc_or_desc; ?>">Pledge Class<i class="fas fa-sort<?php echo $column == 'pc' ? '-' . $up_or_down : ''; ?>"></i></a></th>
		<th><a href="admin.php?column=email&order=<?php echo $asc_or_desc; ?>">School Email<i class="fas fa-sort<?php echo $column == 'email' ? '-' . $up_or_down : ''; ?>"></i></a></th>
		<th><a href="admin.php?column=phone&order=<?php echo $asc_or_desc; ?>">Phone<i class="fas fa-sort<?php echo $column == 'phone' ? '-' . $up_or_down : ''; ?>"></i></a></th>
		<th><a href="admin.php?column=city&order=<?php echo $asc_or_desc; ?>">City<i class="fas fa-sort<?php echo $column == 'city' ? '-' . $up_or_down : ''; ?>"></i></a></th>
		<th><a href="admin.php?column=state&order=<?php echo $asc_or_desc; ?>">State<i class="fas fa-sort<?php echo $column == 'state' ? '-' . $up_or_down : ''; ?>"></i></a></th>
		<th></th>

	</tr>
	<?php while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)): ?>
	<tr>
		<?php
		echo "<td><a href='delete.php?id=".urlencode($row['memberID'])."' onclick='return confirm(\"Are you sure?\");'>&#10060</a></td>"; ?>
    <td<?php echo $column == 'lname' ? $highlight : ''; ?>><?php echo $row["fname"]." ".$row['lname']; ?></td>
    <td<?php echo $column == 'pc' ? $highlight : ''; ?>><?php echo $row['pc']; ?></td>
		<td<?php echo $column == 'email' ? $highlight : ''; ?>><?php echo $row['email']; ?></td>
		<td<?php echo $column == 'phone' ? $highlight : ''; ?>><?php echo $row['phone']; ?></td>
		<td<?php echo $column == 'city' ? $highlight : ''; ?>><?php echo $row['city']; ?></td>
		<td<?php echo $column == 'state' ? $highlight : ''; ?>><?php echo $row['state']; ?></td>
    <td>
          <form method="POST" action="update.php">
          <input type="hidden" name="memberID" value=" <?php echo $row["memberID"]; ?>">
          <input type="submit" value="Update">
          </form>
  </td>
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