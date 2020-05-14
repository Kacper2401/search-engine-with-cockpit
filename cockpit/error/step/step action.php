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
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css"/>
		<meta name="author" content="Kacper Przybylski"/>
		<title>Manual - cockpit</title>
	</head>
	<body>
	<header>
        <hr />
	</header>
	<main>
		</div>
		<?php
			try {
				LoginService::checkLoginStatus();
				StepService::displayStepListDelete();
			} 
			catch (PDOException $e) {
				echo "<a href='choice error.php'>Something went wrong... click me to go to main page!</a>";
			}
		?>
		<div id="sideMenu">
			<div id="back">
				<a href='../error step service.php'>Return to error edit</a>
			</div>
			<div id="addStep">
				<a href='add step.php'>Add step</a>
			</div>
		</div>
	</main>
	<style>
		<?php
			include '../../../style/template cockpit.css';
			include '../../../style/cockpit/error/step/step action.css';
		?>
	</style>
	<footer>
		<hr/>
		© 2020
	</footer>
	</body>
</html>