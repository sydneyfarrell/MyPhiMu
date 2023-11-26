<?php 
	require_once("session.php"); 
	require_once("included_functions.php");
	require_once("database.php");

	new_header("Sydney Farrell Senior Project"); 
	$mysqli = Database::dbConnect();
	$mysqli -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	
  	if (isset($_GET["id"]) && $_GET["id"] !== "") {		
	  $query = "delete from Excuse_Form where excuseID = ?";
	  $stmt = $mysqli->prepare($query);
	    $stmt->execute([$_GET["id"]]);

  
		if ($stmt) {
            
			echo "Excuse was successfully deleted";


		}
		else {
			$_SESSION["message"] = "Excuse could not be deleted";

		}
		
		redirect("viewExcuseForms.php");

		
	}
	else {
		$_SESSION["message"] = "Member data could not be found!";
		redirect("admin.php");
	}

new_footer("Sydney Farrell Senior Project");	
Database::dbDisconnect($mysqli);

?>