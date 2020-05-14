<?php
    class ErrorService {
        private static $dbh;

        public static function displayErrorList() {
            self::setConnectPDO();

            $cockpitNameResult = self::$dbh->query("SELECT id, 
														   shortcut 
													FROM   cockpit_list 
													WHERE  active = 'Y'
													ORDER  BY id");

            echo "<ul id='cockpit'>";
            while ($row = $cockpitNameResult->fetch(PDO::FETCH_ASSOC)) {
                $errorNameResult = self::$dbh->prepare("SELECT id,
															   name
														FROM   errors_list 
														WHERE  cockpit_id = :cockpitId
															   AND active = 'Y'
														ORDER  BY name, 
																  number");
                $errorNameResult->bindParam(':cockpitId', $row['id'], PDO::PARAM_INT);
                $errorNameResult->execute();

                echo "<li>
						<a href='cockpit/edit cockpit.php?cockpitId= " . $row['id'] . "'> Cockpit: " . $row['shortcut'] . "</a>
						<br /><br /><br />
					  </li>
                      <ul id='errorName'>";
                while ($row = $errorNameResult->fetch(PDO::FETCH_ASSOC)) {
                    echo "<li>
							<a href='../cockpit/error/error step service.php?&errorId=" . $row['id'] . "'> Error name: " . $row['name'] . "<br /><br />" . "</a>
						  </li>";
                }
                echo "</ul>
					  <br />";
            }
            echo "</ul>";

            self::$dbh = null;
        }
		
		public static function addError() {
			if(empty($_POST['errorName']) || empty($_POST['cockpitName'])) {
				return;
			}
			self::setConnectPDO();
								
			$cockpitIdResult = self::$dbh->prepare("SELECT id 
													FROM   cockpit_list 
													WHERE  (Lower(full_name) = Lower(:cockpitName) 
														       OR Lower(shortcut) = Lower(:cockpitName))
														   AND active = 'Y'");
			$cockpitIdResult->bindParam(':cockpitName', $_POST['cockpitName'], PDO::PARAM_STR);
			$cockpitIdResult->execute();
			
			$cockpitId = $cockpitIdResult->fetch(PDO::FETCH_ASSOC);
			
			$stat = self::$dbh->prepare("INSERT INTO errors_list
										      	     SELECT NULL,
															Max(number) + 1,
															:errorName,
															:cockpitId,
															'Y'
													 FROM   errors_list
													 WHERE  cockpit_id = :cockpitId
															AND active = 'Y'");
			$stat->bindParam(':errorName', $_POST['errorName'], PDO::PARAM_STR);
			$stat->bindParam(':cockpitId', $cockpitId['id'], PDO::PARAM_INT);
			$stat->execute();
			
			$errorNumberResult = self::$dbh->prepare("SELECT number
													  FROM   errors_list
													  WHERE  name = :errorName
															 AND cockpit_id = :cockpitId
															 AND active = 'Y'");
			$errorNumberResult->bindParam(':errorName', $_POST['errorName'], PDO::PARAM_STR);
			$errorNumberResult->bindParam(':cockpitId', $cockpitId['id'], PDO::PARAM_INT);
			$errorNumberResult->execute();

			session_start();
			
			$errorNumber = $errorNumberResult->fetch(PDO::FETCH_ASSOC);
			
			$_SESSION['errorNumber'] = $errorNumber['number'];
			$_SESSION['cockpitId'] = $cockpitId['id'];

			header('Location: error step service.php');

			self::$dbh = null;
		}
		
		public static function setErrorNumber() {
			self::setConnectPDO();
			
			if(empty($_GET['errorId'])){
				$errorIdResult = self::$dbh->prepare("SELECT id
													  FROM   errors_list
													  WHERE  number = :errorNumber
															 AND cockpit_id = :cockpitId");
				$errorIdResult->bindParam(':errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);											 
				$errorIdResult->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);										 
				$errorIdResult->execute();								   
										
				$errorId = $errorIdResult->fetch(PDO::FETCH_ASSOC);								
				
			    $_SESSION['errorId'] = $errorId['id'];
				
				return;
			}

			$_SESSION['errorId'] = $_GET['errorId'];
						
			$errorNumberResult = self::$dbh->prepare("SELECT number,
															 cockpit_id
													  FROM   errors_list
													  WHERE  id = :errorId
															 AND active = 'Y'");
			$errorNumberResult->bindParam(":errorId", $_GET['errorId'], PDO::PARAM_INT);
			$errorNumberResult->execute();
			
			if($errorNumberResult->rowCount() != 1) {
				header('Location: ../index.php');
				return;
			}
				
			$errorNumber = $errorNumberResult->fetch(PDO::FETCH_ASSOC);
				
			$_SESSION['errorNumber'] = $errorNumber['number'];
			$_SESSION['cockpitId'] = $errorNumber['cockpit_id'];
					
			self::$dbh = null;
		}
		
		public static function createErrorCockpit() {
			
			self::setConnectPDO();
			
			$errorNameResult = self::$dbh->prepare("SELECT name,
														   full_name
													FROM   errors_list
												           LEFT JOIN cockpit_list
															      ON cockpit_list.id = errors_list.cockpit_id
													WHERE  errors_list.active = 'Y'
														   AND errors_list.id = :errorId");
			$errorNameResult->bindParam(':errorId', $_SESSION['errorId'], PDO::PARAM_INT);
			$errorNameResult->execute();

			$errorName = $errorNameResult->fetch(PDO::FETCH_ASSOC);
			
			echo "Error name: <b>" . $errorName['name'] . "</b>
				  <br /><br />
				  Cockpit: <b>  " . $errorName['full_name'] . "</b> 
				  <br/ ><br/ ><br/ ><br/ ><br/ ><br/ ><br/ ><br/ ><br/ ><br/ >
				  <button name='addStep' class='button' onclick='window.location.href=\"add manual.php\";'> Include <br /> instructions </button>
				  <button name='addStep' class='button' onclick='window.location.href=\"step/add step.php\";'> Add <br /> step </button>
			      <button name='editStep' class='button' onclick='window.location.href=\"step/step action.php\";'> Edit <br /> step </button>
			      <form action='' method='POST'>
					  <button name='deleteError' class='button' value='1' onclick='return  confirm(\"Do you want to delete error " . $errorName['name'] . " along with the instructions? \")'> Delete <br /> error </button>
					  <button name='deleteError' class='button' value='2' onclick='return  confirm(\"Do you want to delete error " . $errorName['name'] . "? \")'> Delete only <br /> error name </button>
				  </form>
				  <button name='decisionTree' class='button' onclick='window.location.href=\"decision tree/decision tree service.php\";'> Decision tree </button>
			      <button name='return' class='button' onclick='window.location.href=\"../index.php\";'> Back to error list </button>";
			
			self::$dbh = null;
		}
		
		public static function deleteError() {
			if(empty($_POST['deleteError'])) {
				return;
			}

			self::setConnectPDO();
			
			$checkManualStatusResult = self::$dbh->prepare("SELECT id
															FROM   errors_list
															WHERE  errors_list.number = :errorNumber
																   AND cockpit_id = :cockpitId
																   AND active = 'Y'");
			$checkManualStatusResult->bindParam(":errorNumber", $_SESSION['errorNumber'], PDO::PARAM_INT);
			$checkManualStatusResult->bindParam(":cockpitId", $_SESSION['cockpitId'], PDO::PARAM_INT);
			$checkManualStatusResult->execute();

			if($_POST['deleteError'] == 1 || $checkManualStatusResult->rowCount() == 1) {
				$stat = self::$dbh->prepare("UPDATE errors_list
													left JOIN decision_tree_images
														   ON decision_tree_images.error_number = errors_list.number
													left JOIN steps_resolve
														   ON steps_resolve.error_number = errors_list.number
													left JOIN steps_resolve_image
														   ON steps_resolve_image.steps_resolve_id = steps_resolve.number   
											 SET    decision_tree_images.active = 'N',
													errors_list.active = 'N',
													steps_resolve.active = 'N',
													steps_resolve_image.active = 'N'
											 WHERE  errors_list.number = :errorNumber
													AND errors_list.cockpit_id = :cockpitId");
				$stat->bindParam(":errorNumber", $_SESSION['errorNumber'], PDO::PARAM_INT);
				$stat->bindParam(":cockpitId", $_SESSION['cockpitId'], PDO::PARAM_INT);
				$stat->execute();		
			}
			else {
				
				$stat = self::$dbh->prepare("UPDATE errors_list
										     SET    active = 'N'
											 WHERE  id= :errorId");
				$stat->bindParam(":errorId", $_SESSION['errorId'], PDO::PARAM_INT);
				$stat->execute();
				
				
			}
			
			unset($_SESSION['errorNumber']);
			unset($_SESSION['cockpitId']);
			
			self::$dbh = null;
		}
		
		public static function generateCockpitList() {
			self::setConnectPDO();
			
			$cockpitListResult = self::$dbh->prepare("SELECT full_name
													  FROM   cockpit_list
													  WHERE  active = 'Y'
													  ORDER  BY full_name");
			$cockpitListResult->execute();
			
			echo "<select name='cockpitName'>";
			while($row = $cockpitListResult->fetch(PDO::FETCH_ASSOC)) {
				echo "<option value=" . $row['full_name'] . ">". $row['full_name'] . "</option>";
			}
			echo "</select>";
			
			self::$dbh = null;
		}
		
		public static function changeManual() {
			if(empty($_POST['include'])) {
				return;
			}
			
			self::setConnectPDO();
			
			$errorCountResult = self::$dbh->prepare("SELECT id
													 FROM   errors_list
													 WHERE  number = :errorNumber
															AND cockpit_id = :cockpitId
															AND active = 'Y'");
			$errorCountResult->bindParam(':errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);
			$errorCountResult->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);
			$errorCountResult->execute();
			if($errorCountResult->rowCount() == 1) {
				$stat = self::$dbh->prepare("UPDATE steps_resolve
													LEFT JOIN decision_tree_images
														   ON decision_tree_images.error_number = steps_resolve.error_number
													LEFT JOIN steps_resolve_image
														   ON steps_resolve_image.steps_resolve_id = steps_resolve.id
											 SET    decision_tree_images.active = 'N',
													steps_resolve.active = 'N',
													steps_resolve_image.active = 'N'
											 WHERE  steps_resolve.error_number = :errorNumber
													AND steps_resolve.cockpit_id = :cockpitId ");
				$stat->bindParam(":errorNumber", $_SESSION['errorNumber'], PDO::PARAM_INT);
				$stat->bindParam(":cockpitId", $_SESSION['cockpitId'], PDO::PARAM_INT);
				$stat->execute();
			}
			
			$stat = self::$dbh->prepare("UPDATE errors_list
										 SET    number = :newNumber
										 WHERE  id = :oldNumber");
		    $stat->bindParam(":newNumber", $_POST['errorNumber'], PDO::PARAM_INT);
		    $stat->bindParam(":oldNumber", $_SESSION['errorId'], PDO::PARAM_INT);
			$stat->execute();
			
			$_SESSION['errorNumber'] = $_POST['errorNumber'];
			
			header('Location: ../index.php');
			
			self::$dbh = null;
		}
		
		public static function displayAddManualCockpit() {
			self::setConnectPDO();
			
			$errorListResult = self::$dbh->prepare("SELECT number,
														   name
													FROM   errors_list
													WHERE  cockpit_id = :cockpitId
														   AND active = 'Y'
													ORDER  BY number");
			$errorListResult->bindParam(":cockpitId", $_SESSION['cockpitId'], PDO::PARAM_INT);
			$errorListResult->execute();
			
			echo "Include instructions from error: 
				  <br />
				  <form action='' method='POST'>
					<select name='errorNumber'>";
			while($row = $errorListResult->fetch(PDO::FETCH_ASSOC)) {
				echo "<option value=" . $row['number'] . ">
						". $row['name'] . "
					  </option>";
			}
			echo "	</select> 
					<br /><br /><br /><br />
					<button class='button' name='include' value='1'>Include instructions</button>
				  </form>
				  <br /><br />
				  <button class='button' onclick='window.location.href=\"error step service.php\";'>Return to <br />error edit</button>";
			
			self::$dbh = null;
		}
		
		
        private static function setConnectPDO() {
            self::$dbh = Connect::getConnect();
        }

    }