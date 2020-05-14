<?php
    class DecisionTreeService {
        private static $dbh;
		
		public static function createDecisionTreeCockpit() {
			self::setConnectPDO();
			
			$decisionTreeResult = self::$dbh->prepare("SELECT id,
															  decision_tree
													   FROM   decision_tree_images 
													   WHERE  error_number = :errorNumber
															  AND cockpit_id = :cockpitId
															  AND active = 'Y'");
			$decisionTreeResult->bindParam(':errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);											  
			$decisionTreeResult->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);		
			$decisionTreeResult->execute();
		
			
			if($decisionTreeResult->rowCount() == 0) {
				echo "<button name='addDecisionTree' class='button' onclick='window.location.href=\"add decision tree.php\";' value='1'> Add <br /> tree </button>";
			}
			else {
				echo "<form action='' method='POST'>
						<button name='deleteDecisionTree' class='button' value='1' onclick='return  confirm(\"Do you want to delete decision tree?\")'> Delete <br /> tree </button>
					  </form>";
					  
				
			}
			
			echo "<button name='return' class='button' onclick='window.location.href=\"../error step service.php\";'> Return to error edit </button>";
			
			if($decisionTreeResult->rowCount() == 1) {
				
				echo "<br /><br />
				      Currently decision tree:
					  <br />";
				
				$row = $decisionTreeResult->fetch(PDO::FETCH_ASSOC);
				
				echo "<img src='data:image/jpeg;base64," . base64_encode($row['decision_tree']) . "'>
					  <br /><br />";
			}
			      
			self::$dbh = null;
		}
		
		public static function deleteDecisionTree() {
			if(empty($_POST['deleteDecisionTree']) || !empty($_POST['addDecisionTree'])) {
				return;
			}
			
			self::setConnectPDO();
			
			$stat = self::$dbh->prepare("UPDATE decision_tree_images
									     SET    active = 'N'
									     WHERE  error_number = :errorNumber
										    	AND cockpit_id = :cockpitId
											    AND active = 'Y'");
			$stat->bindParam(':errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);											  
			$stat->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);		
			$stat->execute();
			
			header('Location: decision tree service.php');
			
			self::$dbh = null;
		}
		
		public static function addDecisionTree() {		
			if(empty($_FILES['DecisionTreeData']['tmp_name']) || $_FILES['DecisionTreeData']['error'] != UPLOAD_ERR_OK) {
				return;
			}				
				
			self::setConnectPDO();
			
			$fileData = fopen($_FILES['DecisionTreeData']['tmp_name'], 'rb');
			
			$stat = self::$dbh->prepare("INSERT INTO decision_tree_images 
													 (
														decision_tree,
														error_number,
														cockpit_id,
														active
													 )
										 VALUES      (
														:decisionTreeData, 
														:errorNumber,
														:cockpitId,
														'Y'
											         )");
			$stat->bindParam(':decisionTreeData', $fileData, PDO::PARAM_LOB);
			$stat->bindParam(':errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);
			$stat->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);
			$stat->execute();

			header('Location: decision tree service.php');

			self::$dbh = null;
		}
		
		private static function setConnectPDO() {
            self::$dbh = Connect::getConnect();
        }
	}