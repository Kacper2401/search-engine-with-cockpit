<?php
    class AdditionalMaterialService {
        private static $dbh;

		public static function uploadFile() {
				
			if(empty($_FILES['additionalFileData']['tmp_name']) || $_FILES['additionalFileData']['error'] != UPLOAD_ERR_OK) {
				return;
			}		
				
			self::setConnectPDO();
			
			$fileName = explode('.', $_FILES['additionalFileData']['name']);  
			
			$fileData = fopen($_FILES['additionalFileData']['tmp_name'], 'rb');
			
			$stat = self::$dbh->prepare("INSERT INTO additional_materials 
													 (
														file,
														name
													 )
										 VALUES      (
														:additionalFileData, 
														:fileName
											         )");
			$stat->bindParam(':additionalFileData', $fileData, PDO::PARAM_LOB);
			$stat->bindParam(':fileName', $fileName[0], PDO::PARAM_STR);
			$stat->execute();	

			self::$dbh = null;
		}
		
		public static function displayAdditionalMaterialDelete() {
			self::setConnectPDO();
			
			$additionalMaterialNameResult = self::$dbh->query("SELECT id,
																	  name
															   FROM   additional_materials
															   WHERE  active = 'Y'");
														 
			echo "<ul>";
			while($row = $additionalMaterialNameResult->fetch(PDO::FETCH_ASSOC)) {
				echo "<li>
						<a href='delete additional material.php?additionalMaterialId=" . $row['id'] . "' onclick='return  confirm(\"Do you want to delete file " . $row['name'] . " ?\")'>" . $row['name'] . "<a/>
					  </li>";
			}
			echo "</ul>";
		
			self::$dbh = null;	
		}
		
		public static function displayAdditionalMaterialEdit() {
			self::setConnectPDO();
			
			$additionalMaterialNameResult = self::$dbh->query("SELECT id,
																	  name
															   FROM   additional_materials
															   WHERE  active = 'Y'");
														 
			echo "<ul>";
			while($row = $additionalMaterialNameResult->fetch(PDO::FETCH_ASSOC)) {
				echo "<li>
						<a href='edit additional material.php?additionalMaterialId=" . $row['id'] . "'>" . $row['name'] . "<a/>
					  </li>";
			}
			echo "</ul>";
		
			self::$dbh = null;	
		}
		
		public static function deleteAdditionalMaterial() {
			if(!isset($_GET['additionalMaterialId'])){
				return;
			}
			
			self::setConnectPDO();
			
			$stat = self::$dbh->prepare("UPDATE additional_materials 
										 SET    active = 'N'
										 WHERE  id = :additionalMaterialId
										        AND active = 'Y'");
			$stat->bindParam(":additionalMaterialId", $_GET['additionalMaterialId'], PDO::PARAM_STR);
			$stat->execute();
			
			header("Location: delete additional material.php");
			
			self::$dbh = null;
		}
		
		public static function displayAdditionalMaterialToEdit() {
			self::setConnectPDO();
			
			$additionalMaterialResult = self::$dbh->prepare("SELECT name,
																	file
															 FROM   additional_materials
															 WHERE  id = :additionalMaterialId");
			$additionalMaterialResult->bindParam(':additionalMaterialId', $_GET['additionalMaterialId'], PDO::PARAM_INT);
			$additionalMaterialResult->execute();
			
			$additionalMaterialData = $additionalMaterialResult->fetch(PDO::FETCH_ASSOC);
			
	        echo '  <div id="name">
						<b>Change additional material</b>
						<br /><br />
						Additional material: ' . $additionalMaterialData['name'] . '
					</div>
					<div id="edit">
					<form method="POST" action="" enctype="multipart/form-data">
						<div id="additionalMaterialName">
							Additional material name
							<br />
							<input type="text" name="additionalMaterialName" value=" ' . $additionalMaterialData['name'] . ' ">
						</div>
						<div id="additionalMaterialFile">
							File
							<br />
							<input type="file" name="additionalFileData">
						</div>
						<br /><br /><br />
						<button name="send" class="button" type="submit" value="1">Edit!</button>
					</form>';

			self::$dbh = null;
		}
		
		public static function updateAdditionalMaterial() {
			if(empty($_POST['send'])) {
				return;
			}
			
			self::setConnectPDO();
			
			$setFile = " ";
			
			if(!empty($_FILES['additionalFileData']['tmp_name'])) {
				if($_FILES['additionalFileData']['error'] == UPLOAD_ERR_OK) {
					$fileName = explode('.', $_FILES['additionalFileData']['name']);  
			
					$fileData = fopen($_FILES['additionalFileData']['tmp_name'], 'rb');
					
					$setFile = ", file = :additionalFileData ";
				}
			}
			
			$stat = self::$dbh->prepare("UPDATE additional_materials
										 SET    name = Trim(:additionalMaterialName)
												" . $setFile . "
										 WHERE  id = :additionalMaterialId
												");	
			$stat->bindParam(':additionalMaterialId', $_GET['additionalMaterialId'], PDO::PARAM_INT);
			if(!empty($_FILES['additionalFileData']['tmp_name'])) {
				$stat->bindParam(':additionalFileData', $fileData, PDO::PARAM_LOB);
			}
			$stat->bindParam(':additionalMaterialName', $_POST['additionalMaterialName'], PDO::PARAM_STR);
			$stat->execute();
			
			self::$dbh = null;
		}
		
		private static function setConnectPDO() {
            self::$dbh = Connect::getConnect();
        }
		
    }