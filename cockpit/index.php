<?php
	include_once("class/LoginService.php");
    include_once("../ConnectLogin.php");
	session_start();
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8"/>
		<meta name="viewport"
			  content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"/>
		<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
		<link rel="stylesheet" href="../style/cockpit/index.css"/>
		<link rel="stylesheet" href="../style/template cockpit.css"/>
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
			LoginService::logIn();
		} 
		catch (PDOException $e) {
			echo $e;
		}
		?>
			 <form action="index.php" method="post">
				<div id="login">
					<div class="text">
						Login
					</div>
					<br>
					<input type="text" name="login">
				</div>
				<div id="password">
					Password
					<br>
					<input type="password" name="password">
				</div>
				<br><br>
				<button type="submit" class="button">Login!</button>
			</form>
	</main>
	<footer>
		<hr/>
		© 2020
	</footer>
	</body>
</html>