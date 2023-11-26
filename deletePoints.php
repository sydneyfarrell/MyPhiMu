<?php 
	require_once("session.php"); 
	require_once("included_functions.php");
	require_once("database.php");

	new_header("Sydney Farrell Senior Project"); 
	$mysqli = Database::dbConnect();
	$mysqli -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	
  	if (isset($_GET["id"]) && $_GET["id"] !== "") {		
	  $query = "delete from Points where pointID = ?";
	  $stmt = $mysqli->prepare($query);
	    $stmt->execute([$_GET["id"]]);

  
		if ($stmt) {
            
			echo "Point was successfully deleted";


		}
		else {
			$_SESSION["message"] = "Point could not be deleted";

		}
		
		redirect("viewMemberPoints.php");

		
	}
	else {
		$_SESSION["message"] = "Member data could not be found!";
		redirect("admin.php");
	}

new_footer("Sydney Farrell Senior Project");	
Database::dbDisconnect($mysqli);

?>