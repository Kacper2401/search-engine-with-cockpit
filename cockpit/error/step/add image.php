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
		<?php
			echo "Add image <br /><br /> Step number <b>" . $_SESSION['stepNumber'] . "</b>";
		?>
		<div id="add">
			<form action="" method="POST" enctype="multipart/form-data">
				<div id="image">
					<input type="file" name="image">
				</div>
				<br /><br /><br />
				<button class="button" type="submit" >Add<br />image!</button>
			</form>
			<button name='return' class='button' onclick='window.location.href="edit step.php";'> Back to edit step </button>
		</div>
		<?php
			try {
				LoginService::checkLoginStatus();
				StepService::addStepImage();
			} 
			catch (PDOException $e) {
				echo "<a href='choice error.php'>Something went wrong... click me to go to main page!</a>";
			}
		?>
	</main>
	<style>
		<?php
			include '../../../style/template cockpit.css';
			include '../../../style/cockpit/error/step/add image.css';
		?>
	</style>
	<footer>
		<hr/>
		© 2020
	</footer>
	</body>
</html>