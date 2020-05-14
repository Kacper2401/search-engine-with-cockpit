<?php
	session_start();
    include_once("../../../Connect.php");
	include_once("../../class/LoginService.php");
    include_once("../../class/StepService.php");
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8"/>
		<meta name="viewport"
			  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"/>
		<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
		<link rel="stylesheet" href="../../../style/template cockpit.css"/>
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css"/>
		<meta name="author" content="Kacper Przybylski"/>
		<title>Manual - cockpit</title>
	</head>
	<body>
	<header>
        <hr />
	</header>
	<main>
		<?php
			try {
				LoginService::checkLoginStatus();
				StepService::deleteStep();
				header("Location: step action.php");
			} 
			catch (PDOException $e) {
				echo "<a href='step action.php'>Something went wrong... click me to go to edit step!</a>";
			}
		?>
	</main>
	<footer>
		<hr/>
		© 2020
	</footer>
	</body>
</html>