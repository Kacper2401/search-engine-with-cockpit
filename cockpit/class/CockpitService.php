<?php
    class CockpitService {
        private static $dbh;
		
		public static function addCockpit() {
			if(empty($_POST['cockpitName']) || empty($_POST['cockpitShortcut'])) {
				return;
			}
			
			self::setConnectPDO();
			
			$stat = self::$dbh->prepare("INSERT INTO cockpit_list
													 (
														full_name,
														shortcut
													 )
										 VALUE		 (
														:cockpitName, 
														:cockpitShortcut
													 )");
			$stat->bindParam(":cockpitName", $_POST['cockpitName'], PDO::PARAM_STR);
			$stat->bindParam(":cockpitShortcut", $_POST['cockpitShortcut'], PDO::PARAM_STR);
			$stat->execute();
			
			self::$dbh = null;
		}
		
		public static function displayCockpitListDelete() {
			self::setConnectPDO();
			
			$cockpitNameResult = self::$dbh->query("SELECT id,
														   full_name
												    FROM   cockpit_list
												    WHERE  active = 'Y'");
											  							  
			echo "<ul>";
			while($row = $cockpitNameResult->fetch(PDO::FETCH_ASSOC)) {
				echo "<li>
						<a href='delete cockpit.php?cockpitId=" . $row['id'] . "' onclick='return  confirm(\"Do you want to delete cockpit " . $row['full_name'] . " ?\")'>" . $row['full_name'] . "<a/>
					  </li>";
			}
			echo "</ul>";
		
			self::$dbh = null;
		}
		
		public static function displayCockpitListEdit() {
			self::setConnectPDO();
			
			$cockpitNameResult = self::$dbh->query("SELECT id,
														   full_name
												    FROM   cockpit_list
												    WHERE  active = 'Y'");
											  							  
			echo "<ul>";
			while($row = $cockpitNameResult->fetch(PDO::FETCH_ASSOC)) {
				echo "<li>
						<a href='edit cockpit.php?cockpitId=" . $row['id'] . "'>" . $row['full_name'] . "<a/>
					  </li>";
			}
			echo "</ul>";
		
			self::$dbh = null;
		}
		
		public static function deleteCockpit() {
			if(!isset($_GET['cockpitId'])){
				return;
			}
			self::setConnectPDO();
			
			$stat = self::$dbh->prepare("UPDATE cockpit_list
											    LEFT JOIN decision_tree_images
													   ON decision_tree_images.cockpit_id = cockpit_list.id
											    LEFT JOIN errors_list
													   ON errors_list.cockpit_id = cockpit_list.id
											    LEFT JOIN steps_resolve
												 	   ON steps_resolve.cockpit_id = cockpit_list.id
											    LEFT JOIN steps_resolve_image
													   ON steps_resolve_image.steps_resolve_id = steps_resolve.id
										 SET    cockpit_list.active = 'N',
											    decision_tree_images.active = 'N',
											    errors_list.active = 'N',
											    steps_resolve.active = 'N',
										 	    steps_resolve_image.active = 'N'
										 WHERE  cockpit_list.id = :cockpitId ");
			$stat->bindParam(":cockpitId", $_GET['cockpitId'], PDO::PARAM_INT);
			$stat->execute();
			
			header("Location: delete cockpit.php");
			
			self::$dbh = null;
		}
		
		public static function displayCockpitToEdit(){
			self::setConnectPDO();
			
			$resultCockpitDataResult = self::$dbh->prepare("SELECT full_name,
																   shortcut
														    FROM   cockpit_list
														    WHERE  id = :cockpitId");
			$resultCockpitDataResult->bindParam(':cockpitId', $_GET['cockpitId'], PDO::PARAM_INT);
			$resultCockpitDataResult->execute();
			
			$cockpitData = $resultCockpitDataResult->fetch(PDO::FETCH_ASSOC);
			
	        echo '  <div id="name">
						<b>Change cockpit</b>
						<br /><br />
						Cockpit: ' . $cockpitData['full_name'] . '
					</div>
					<div id="edit">
					<form method="POST" action="">
						<div id="cockpitName">
							Cockpit name
							<br />
							<input type="text" name="cockpitName" value=" ' . $cockpitData['full_name'] . ' ">
						</div>
						<div id="cockpitShortcut">
							Cockpit shortcut
							<br />
							<input type="text" name="cockpitShortcut" value=" ' . $cockpitData['shortcut'] . ' ">
						</div>
						<br /><br /><br />
						<button name="send" class="button" type="submit" value="1">Edit!</button>
					</form>';
					
			self::$dbh = null;
		}
		
		public static function editCockpit() {
			if(empty($_POST['send'])) {
				return;
			}
			
			self::setConnectPDO();
			
			$stat = self::$dbh->prepare("UPDATE cockpit_list
										 SET    full_name = Trim(:cockpitFullName),
												shortcut = Trim(:cockpitShortcut)
										 WHERE  id = :cockpitId
												");
			$stat->bindParam(':cockpitFullName', $_POST['cockpitName'], PDO::PARAM_STR);
			$stat->bindParam(':cockpitShortcut', $_POST['cockpitShortcut'], PDO::PARAM_STR);
			$stat->bindParam(':cockpitId', $_GET['cockpitId'], PDO::PARAM_INT);
			$stat->execute();

			self::$dbh = null;
		}
		
		private static function setConnectPDO() {
            self::$dbh = Connect::getConnect();
        }
    }