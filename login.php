<?php

session_start();

require_once("database.php");

$mysqli = Database::dbConnect();
$mysqli -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function redirectUser($memberID, $mysqli) {
    $stmt = $mysqli->prepare("SELECT * FROM Exec_Member WHERE memberID = ?");
    $stmt->execute([$memberID]);
    
    $_SESSION['memberID'] = $memberID;

    if ($stmt->rowCount() > 0) {
        header("Location: admin.php");
    } else {
        header("Location: member.php");
    }
    exit(); 
}

if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];

    if ($password == 'phimu1852') {
        $_SESSION['email'] = $email;
        header("Location: pwd.php");
        exit();
    } else {
        $stmt = $mysqli->prepare("SELECT * FROM Login WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user["password"])) {
                $stmt = $mysqli->prepare("SELECT * FROM Member WHERE email = ?");
                $stmt->execute([$email]);

                if ($stmt->rowCount() > 0) {
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    redirectUser($user["memberID"], $mysqli);
                } else {
                    echo "Email not found. Please check your credentials.";
                }
            }
        }
    }
}

Database::dbDisconnect($mysqli);
?>

<head>
  <title>MyPhiMu - Login</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
  <link href="/css/" rel="stylesheet" >
<script src="/js/"></script>
  
</head>

<style>
#template-bg-1 {
	background-image: url('img/background.jpg');
    width: 1300px;
    height:600px;
    background-size: cover; 
    background-position: center center;
    background-repeat: no-repeat;
    text-align:center;
    margin:auto;
    padding:0;
    
}

</style>
<body>
<div id="template-bg-1">
    <div
        class="d-flex flex-column min-vh-100 justify-content-center align-items-center">
        <div class="card p-4 text-light bg-light mb-5">
            <div class="card-header" style="background-color: hotpink">
                <h3>MyPhiMu</h3>
            </div>
            <div class="card-body w-100">
                <form name="login" action="" method="POST">
                    <div class="input-group form-group mt-3">
                        <input type="email" class="form-control"
                            placeholder="email" name="email">
                    </div>
                    <div class="input-group form-group mt-3">
                    <input type="password" class="form-control"
                            placeholder="password" name="password">
                    </div>

                    <div class="form-group mt-3">
                        <input type="submit" value="Login"
                            class="btn float-end text-white w-100" style="background-color: hotpink"
                            name="login-btn">
                    </div>
                    </form>
			</div>
        </div>
    </div>
</div>
</body>
</html>