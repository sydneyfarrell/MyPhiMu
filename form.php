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

function redirectUser($memberID, $mysqli) {
    $stmt = $mysqli->prepare("SELECT * FROM Exec_Member WHERE memberID = ?");
    $stmt->execute([$memberID]);

    if ($stmt->rowCount() > 0) {
        header("Location: admin.php");
        exit();
    } else {
        header("Location: member.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $description = $_POST["pointDesc"];
    $category = $_POST["category"];
    $memberID = $_SESSION['memberID'];

    $stmt = $mysqli->prepare("INSERT INTO Points(pointDesc, memberID, typeID) VALUES (?, ?, ?)");
    $stmt->execute([$description, $memberID, $category]);

    if ($stmt->rowCount() > 0) {
        redirectUser($memberID, $mysqli);
    } else {
        echo "Could not submit points form!";
    }
}

$stmt = $mysqli->prepare("SELECT * FROM Point_Type WHERE typeID NOT IN (SELECT typeID FROM Points WHERE memberID = ?)");
$stmt->execute([$memberID]);
$category = $stmt->fetchAll(PDO::FETCH_ASSOC);

Database::dbDisconnect($mysqli);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>MyPhiMu - Points Form</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="/css/" rel="stylesheet">
    <script src="/js/"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #ffb7c5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form {
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            border-radius: 15px;
            overflow: hidden;
            display: flex;
            width: 90%;
            height: 80%;
            max-width: 800px;
            background-color: #fff;
        }

        .form-image {
            flex-shrink: 0;
            width: 45%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-body {
            padding: 20px;
            width: 80%;
            height: 70%;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-top: 50px;
            margin-bottom: 20px;
        }

        .form-submit {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            background-color: #ffb7c5;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            font-weight: bold;
        }

    </style>
</head>

<body>
    <div class="form">
        <div class="form-image">
            <img src="img/bg-heading-02.jpg" style="width: 100%; height: auto;">
        </div>
        <div class="form-body">
            <form name="form" action="" method="POST">
                <div class="form-group">
                <h3 style="color: black; font-weight: bold;">Points Form</h3>
                <select name="category" class="form-control" required>
                    <option disabled="disabled" selected="selected">Choose Category</option>
                    <?php foreach ($category as $c) : ?>
                    <option value="<?php echo $c['typeID']; ?>" class="category"><?php echo $c['typeDesc']; ?></option>
                    <?php endforeach; ?>
                    </select>
                    <div class="select-dropdown"></div>
                    </div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Description" name="pointDesc" required>
         
                </div>
                <div class="form-group">
                    <input type="submit" value="Submit" class="form-submit" name="submit">
                </div>
            </form>
        </div>
    </div>
</body>

</html>