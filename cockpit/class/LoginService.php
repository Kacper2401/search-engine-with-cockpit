<?php
    class LoginService {
        private static $dbh;
		
		public static function addAccount() {
			self::setConnectPDO();
			
			$login = "";
			$password = password_hash("", PASSWORD_ARGON2I);
			
			$stat = self::$dbh->prepare("INSERT INTO data 
									     VALUES	     (NULL, 
													 :login, 
													 :password)");
			$stat->bindParam(':login', $login, PDO::PARAM_STR);
			$stat->bindParam(':password', $password, PDO::PARAM_STR);
			$stat->execute();
			
			self::$dbh = null;
		}
		
		public static function changePassword() {
			self::setConnectPDO();
			 
			$login = "test";
			$password = password_hash("test", PASSWORD_ARGON2I);
			
			$stat = self::$dbh->prepare("UPDATE data
									     SET	password = :password
										 WHERE  login = :login");
			$stat->bindParam(':login', $login, PDO::PARAM_STR);
			$stat->bindParam(':password', $password, PDO::PARAM_STR);
			$stat->execute();
			
			self::$dbh = null;
		}
		
		public static function logIn() {
			self::setConnectPDO();
			
			if(isset($_SESSION['login'])) {
				exit(header("Location: error list.php"));
			}

			
			if(empty($_POST['password']) || empty($_POST['login'])) {
				return;
			}
			
			$hashPassword = password_hash($_POST['password'], PASSWORD_ARGON2I);
			
			$checkAccount = self::$dbh->prepare("SELECT password
											     FROM	data
												 WHERE  login = :login");
			$checkAccount->bindParam(':login', $_POST['login'], PDO::PARAM_STR);
			$checkAccount->execute();
			
			$accountPassword = $checkAccount->fetch(PDO::FETCH_ASSOC);
			if(password_verify($_POST['password'], $accountPassword['password'])) {
				session_start();
				$_SESSION['login'] = 1;
				header("Location: error list.php");
			}
			else {
				echo "Wrong login or password
					  <br><br>";
			}
			
			self::$dbh = null;
		}
		
		public static function checkLoginStatus() {
			if(!isset($_SESSION['login'])) {
				exit(header("Location: ../../../../apka - testy/cockpit/index.php"));
			}
		}
		
		private static function setConnectPDO() {
            self::$dbh = ConnectLogin::getConnect();
        }
	}