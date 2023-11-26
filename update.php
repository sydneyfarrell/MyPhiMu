<?php

session_start();

require_once("database.php");

$mysqli = Database::dbConnect();
$mysqli->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "POST") {
  if((isset($_POST["fname"]) && $_POST["fname"] !== "") &&
  (isset($_POST["lname"]) && $_POST["lname"] !== "") &&
  (isset($_POST["pc"]) && $_POST["pc"] !== "") &&
  (isset($_POST["email"]) && $_POST["email"] !== "") &&
  (isset($_POST["phone"]) && $_POST["phone"] !== "") &&
  (isset($_POST["city"]) && $_POST["city"] !== "") &&
  (isset($_POST["state"]) && $_POST["state"] !== "") &&
  (isset($_POST["memberID"]) && $_POST["memberID"] !== "")) {


    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $pc = $_POST['pc'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $mi = $_POST['memberID'];

    $query = "UPDATE Member SET fname = ?, lname = ?, email = ?, phone = ?, city = ?, state = ?, pc = ? WHERE memberID = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->execute([$fname, $lname, $email, $phone, $city, $state, $pc, $mi]);

    if ($stmt) {
      header("Location: admin.php");
      exit();
		}
		else {
			$_SESSION["message"] = "Member could not be updated";
		}
	}
	else {
		$_SESSION["message"] = "Member data could not be found!";
	} 
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <title>MyPhiMu - Update</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.11.2/css/all.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="css/style.css">
  
</head>

<body>

<!-- Modal -->
<div id="myModal" class="modal-fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">

<?php

  $id = $_POST['memberID'];

  $query = "SELECT memberID, fname, lname, pc, image, email, phone, city, state FROM Member WHERE memberID = ?";
  $stmt = $mysqli->prepare($query);
  $stmt->execute([$id]);

  if ($stmt) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      echo "<h3 class='col-12 modal-title text-center'>" . $row["fname"] . " " . $row['lname'] . "<br />" . "<img src =" . $row["image"] . " width='200'>" . "</h3>";
   
   ?>
      </div>
      <div class="modal-body">
      <form name = "update" action ="" method="POST">
        <input type="hidden" name="memberID" value=" <?php echo $row["memberID"]?>">
        <label for ="fname">Name:</label>
        <input type="text" name = "fname" id="fname" placeholder="<?php echo $row["fname"]?>" value="<?php echo $row["fname"]?>"> 
        <input type="text" name = "lname" id="lname" placeholder="<?php echo $row["lname"]?>" value="<?php echo $row["lname"]?>"> 
        <label for ="pc">Pledge Class:</label>
        <input type="text" name = "pc" id="pc" maxlength="4" size="4" placeholder="<?php echo $row["pc"]?>" value="<?php echo $row["pc"]?>">
        <label for ="phone">Phone:</label>
        <input type="phone" name = "phone" id="phone" maxlength="10" size="10" placeholder="<?php echo $row["phone"]?>" value="<?php echo $row["phone"]?>">
        <br />
        <label for ="email">Email:</label>
        <input type="text" name = "email" id="email" placeholder="<?php echo $row["email"]?>" value="<?php echo $row["email"]?>">
        <br />
        <label for ="city">City:</label>
        <input type="text" name = "city" id="city" placeholder="<?php echo $row["city"]?>" value="<?php echo $row["city"]?>">
        <label for="state">State:</label>
			<select class="dropdown" id="state" name="state">
        <option value="<?php echo $row["state"]?>"><?php echo $row["state"]?></option>
        <option value="Alabama">Alabama</option>
        <option value="Alaska">Alaska</option>
        <option value="Arizona">Arizona</option>
        <option value="Arkansas">Arkansas</option>
        <option value="California">California</option>
        <option value="Colorado">Colorado</option>
        <option value="Connecticut">Connecticut</option>
        <option value="Delaware">Delaware</option>
        <option value="District of Columbia">District of Columbia</option>
        <option value="Florida">Florida</option>
        <option value="Georgia">Georgia</option>
        <option value="Guam">Guam</option>
        <option value="Hawaii">Hawaii</option>
        <option value="Idaho">Idaho</option>
        <option value="Illinois">Illinois</option>
        <option value="Indiana">Indiana</option>
        <option value="Iowa">Iowa</option>
        <option value="Kansas">Kansas</option>
        <option value="Kentucky">Kentucky</option>
        <option value="Louisiana">Louisiana</option>
        <option value="Maine">Maine</option>
        <option value="Maryland">Maryland</option>
        <option value="Massachusetts">Massachusetts</option>
        <option value="Michigan">Michigan</option>
        <option value="Minnesota">Minnesota</option>
        <option value="Mississippi">Mississippi</option>
        <option value="Missouri">Missouri</option>
        <option value="Montana">Montana</option>
        <option value="Nebraska">Nebraska</option>
        <option value="Nevada">Nevada</option>
        <option value="New Hampshire">New Hampshire</option>
        <option value="New Jersey">New Jersey</option>
        <option value="New Mexico">New Mexico</option>
        <option value="New York">New York</option>
        <option value="North Carolina">North Carolina</option>
        <option value="North Dakota">North Dakota</option>
        <option value="Ohio">Ohio</option>
        <option value="Oklahoma">Oklahoma</option>
        <option value="Oregon">Oregon</option>
        <option value="Pennsylvania">Pennsylvania</option>
        <option value="Puerto Rico">Puerto Rico</option>
        <option value="Rhode Island">Rhode Island</option>
        <option value="South Carolina">South Carolina</option>
        <option value="South Dakota">South Dakota</option>
        <option value="Tennessee">Tennessee</option>
        <option value="Texas">Texas</option>
        <option value="Utah">Utah</option>
        <option value="Vermont">Vermont</option>
        <option value="Virginia">Virginia</option>
        <option value="Virgin Islands">Virgin Islands</option>
        <option value="Washington">Washington</option>
        <option value="West Virginia">West Virginia</option>
        <option value="Wisconsin">Wisconsin</option>
        <option value="Wyoming">Wyoming</option></select>
      </div>
      <div class="modal-footer">
      <button type="submit" value = "Update" name = "submit" class="btn btn-secondary" data-bs-dismiss="modal">Update</button>
      </div>
    </form>
    </div>
  </div>
</div>

<?php
} 
}
?>

</body>
</html>

<?php

Database::dbDisconnect($mysqli);
?>