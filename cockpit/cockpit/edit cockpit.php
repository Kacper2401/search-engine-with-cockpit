<?php
	session_start();
    include_once("../../Connect.php");
	include_once("../class/LoginService.php");
    include_once("../class/CockpitService.php");
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8"/>
		<meta name="viewport"
			  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"/>
		<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
		<link rel="stylesheet" href="../../style/template cockpit.css"/>
		<link rel="stylesheet" href="../../style/cockpit/cockpit/edit cockpit.css"/>
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css"/>
		<meta name="author" content="Kacper Przybylski"/>
		<title>Manual - cockpit</title>
	</head>
	<body>
	<header>
        <hr />
	</header>
	<main>
        <div id="sideMenu">
			<div id="chooseError">
				<a href="../index.php">Choose error</a>
			</div>
			<div id="addError">
				<a href="../error/add error.php">Add error</a>
			</div>
			<div id="addCockpit">
				<a href="add cockpit.php">Add cockpit</a>
			</div>
			<div id="editCockpit">
				<a href="list cockpit.php">Change cockpit name</a>
			</div>
			<div id="deleteCockpit">
				<a href="delete cockpit.php">Delete cockpit</a>
			</div>
			<div id="addMaterial">
				<a href="../additional material/add additional material.php">Add additional material</a>
			</div>
			<div id="editMaterial">
				<a href="../additional material/list additional material.php">Edit additional material</a>
			</div>
			<div id="deleteMaterial">
				<a href="../additional material/delete additional material.php">Delete additional material</a>
			</div>
			</div>
			<?php
				try {
					LoginService::checkLoginStatus();
					CockpitService::editCockpit();
					CockpitService::displayCockpitToEdit();
				}
				catch (PDOException $e) {
					echo "<a href='choice error.php'>Something went wrong... click me to go to main page!</a>";
				}
			?>
		</div>
	</main>
	<style>
		<?php
			include '../../style/cockpit/cockpit/edit cockpit.css';
		?>
	</style>
	<footer>
		<hr/>
		© 2020
	</footer>
	</body>
</html>