<?php
    class StepService {
        private static $dbh;
		
		public static function displayStepListDelete() {
			self::setConnectPDO();
		
			$stepListResult = self::$dbh->prepare("SELECT number
												   FROM   steps_resolve
												   WHERE  error_number = :errorNumber
														  AND cockpit_id = :cockpitId
													      AND active = 'Y'
												   ORDER  BY number");
			$stepListResult->bindParam(':errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);
			$stepListResult->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);
			$stepListResult->execute();
			
			echo "<ul>";
			while($row = $stepListResult->fetch(PDO::FETCH_ASSOC)) {
				if($row['number'] < 10){
					echo "<li>
							Step " . $row['number'] . "&nbsp&nbsp;&nbsp;&nbsp;
							<form action='edit step.php' method='POST'>
								<button class='button'>edit</button>
								<input type='text' name='stepNumber' value=' " . $row['number'] . " ' hidden>
							</form> 
							<form action='step tmp.php' method='POST'>							
								<button class='button'  onclick='return  confirm(\"Do you want to delete step number " . $row['number'] . " ?\")'>delete</button>
								<input type='text' name='stepNumber' value=' " . $row['number'] . " ' hidden>
							</form>
						 </li>";
				}
				elseif($row['number'] < 100) {
					echo "<li>
							Step " . $row['number'] . "&nbsp;&nbsp;
							<form action='edit step.php' method='POST'>
								<button class='button'>edit</button>
								<input type='text' name='stepNumber' value=' " . $row['number'] . " ' hidden>
							</form> 
							<form action='step tmp.php' method='POST'>							
								<button class='button' onclick='return  confirm(\"Do you want to delete step number " . $row['number'] . " ?\")'>delete</button>
								<input type='text' name='stepNumber' value=' " . $row['number'] . " ' hidden>
							</form>
						 </li>";
				}
				else {
					echo "<li>
							Step " . $row['number'] . "&nbsp;
							<form action='edit step.php' method='POST'>
								<button class='button' onclick='return  confirm(\"Do you want to delete step number " . $row['number'] . " ?\")'>edit</button>
								<input type='text' name='stepNumber' value=' " . $row['number'] . " ' hidden>
							</form> 
							<form action='step tmp.php' method='POST'>							
								<button class='button'>delete</button>
								<input type='text' name='stepNumber' value=' " . $row['number'] . " ' hidden>
							</form>
						 </li>";
				}
				echo "<br />";
			}
			echo "</ul>";
			
			self::$dbh = null;
		}
		
		public static function deleteStep() {
			if(empty($_POST['stepNumber'])) {
				return;
			}
			
			self::setConnectPDO();
			
			$stat = self::$dbh->prepare("UPDATE steps_resolve
										 SET    active = 'N'
										 WHERE  number = :stepNumber
											    AND cockpit_id = :cockpitId
												AND error_number = :errorNumber
												AND active = 'Y'");
			$stat->bindParam(':stepNumber', $_POST['stepNumber'], PDO::PARAM_INT);
			$stat->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);
			$stat->bindParam(':errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);
			$stat->execute();
			
			$stat->closeCursor();
			
			self::changeReferenceDelete();
			
			self::$dbh = null;
		}
		
		public static function createEditStepCockpit() {
			if(empty($_POST['stepNumber']) && empty($_SESSION['stepNumber'])) {
				return;
			}
			
			if(!empty($_POST['stepNumber'])) {
				$_SESSION['stepNumber'] = $_POST['stepNumber'];
			}
			
			self::setConnectPDO();
			
			$stepDataResult = self::$dbh->prepare("SELECT description,
														  next_step_question,
														  next_step_option,
														  next_step_possible_number
												   FROM   steps_resolve
												   WHERE  number = :stepNumber
														  AND cockpit_id = :cockpitId
														  AND error_number = :errorNumber
														  AND active = 'Y'");
			$stepDataResult->bindParam(':stepNumber', $_SESSION['stepNumber'], PDO::PARAM_INT);
			$stepDataResult->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);
			$stepDataResult->bindParam(':errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);
			$stepDataResult->execute();
			
			$stepData = $stepDataResult->fetch(PDO::FETCH_ASSOC);
				
			echo "	To add several options, separate them with ';' Example: 
			        <br />
					first option;second option;third option 
					<br /><br />
					Step number:<b> " . $_SESSION['stepNumber'] . "</b>
					<br /><br /><br /><br />
					<form action='' method='POST'> 
						<div id = 'stepDescription'>
							Description <br /><textarea rows='4' cols='50' name='stepDescription'>" . $stepData['description'] . "</textarea>
						</div>
						<div id = 'nextStepQuestion'>
							Split question <br /><textarea rows='4' cols='50' name='nextStepQustion'>" . $stepData['next_step_question'] . "</textarea>
						</div>
						<div id = 'nextStepOption'>
							Option<br /><textarea rows='4' cols='50' name='nextStepOption'>" . $stepData['next_step_option'] . "</textarea>
						</div>
						<div id = 'nextStepPossibleNumber'>
							Next step number <br /><textarea rows='4' cols='50' name='nextStepPossibleNumber'>" . $stepData['next_step_possible_number'] . "</textarea>
						</div>
						<br /><br /><br /><br />
						<button class='button' name='step' value='1'>Change <br />step</button>
				    </form>
					<br /><br />
					<div id='cockpit'>
						<button class='button' onclick='window.location.href=\"add image.php\";'>Add <br />image </button>
						<button class='button' onclick='window.location.href=\"delete image.php\";'>Delete image </button>
						<button class='button' onclick='window.location.href=\"step action.php\";'>Return to step list</button>
					</div>";
			
			self::$dbh = null;
		}
		
		public static function changeStep() {
			
			if(!self::checkEmptyFields()) {
				return;
			}
			
			if(!self::checkNumberOfBranches()) {
				return;
			}
			
			self::setConnectPDO();
			
			$stat = self::$dbh->prepare("UPDATE steps_resolve
										 SET    description = Trim(:stepDescription),
												next_step_question = Trim(:nextStepQustion),
												next_step_option = Trim(:nextStepOption),
												next_step_possible_number = Trim(:nextStepPossibleNumber)
										 WHERE  cockpit_id = :cockpitId 
												AND number = :stepNumer
												AND error_number = :erroNumber
												AND active = 'Y'");
			$stat->bindParam(":stepDescription", $_POST['stepDescription'], PDO::PARAM_STR);
			$stat->bindParam(":nextStepQustion", $_POST['nextStepQustion'], PDO::PARAM_STR);
			$stat->bindParam(":nextStepOption", $_POST['nextStepOption'], PDO::PARAM_STR);
			$stat->bindParam(":nextStepPossibleNumber", $_POST['nextStepPossibleNumber'], PDO::PARAM_STR);
			$stat->bindParam(":cockpitId", $_SESSION['cockpitId'], PDO::PARAM_INT);
			$stat->bindParam(":stepNumer", $_SESSION['stepNumber'], PDO::PARAM_INT);
			$stat->bindParam(":erroNumber", $_SESSION['errorNumber'], PDO::PARAM_INT);
			$stat->execute();
			
			echo "<script>
					 alert('Change has been save');
				  </script>";
			
			self::$dbh = null;
		}
		
		public static function addStepImage() {
			if(empty($_FILES['image']['tmp_name']) || $_FILES['image']['error'] != UPLOAD_ERR_OK) {
				return;
			}			

			self::setConnectPDO();
			
			$fileData = fopen($_FILES['image']['tmp_name'], 'rb');
			
			$stat = self::$dbh->prepare("INSERT INTO steps_resolve_image 
													 SELECT NULL,
															:image,
															id,
															'Y'
													 FROM   steps_resolve
													 WHERE  cockpit_id = :cockpitId
															AND error_number = :errorNumber
															AND number = :stepNumber
															AND active = 'Y'");
			$stat->bindParam(':image', $fileData, PDO::PARAM_LOB);
			$stat->bindParam(':errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);
			$stat->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);
			$stat->bindParam(':stepNumber', $_SESSION['stepNumber'], PDO::PARAM_INT);
			$stat->execute();	
			
		   echo "<script>
					alert('image successfully add');
				 </script>";

			self::$dbh = null;
		}
		
		public static function displayStepImage() {
			self::setConnectPDO();
			
            $imageResult = self::$dbh->prepare("SELECT image,
													   id
											    FROM   steps_resolve_image
											    WHERE  steps_resolve_id = (SELECT id 
																		   FROM   steps_resolve
																		   WHERE  number = :stepNumber
																				  AND cockpit_id = :cockpitId
																				  AND error_number = :errorNumber
																				  AND active = 'Y'
																		  )
													   AND active = 'Y'");
            $imageResult->bindParam(':stepNumber', $_SESSION['stepNumber'], PDO::PARAM_INT);
            $imageResult->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);
            $imageResult->bindParam(':errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);
            $imageResult->execute();
			
			echo 'To delete an image click on it <br /><br /><br />';
			$i = 0;
            while($row = $imageResult->fetch(PDO::FETCH_ASSOC)) {
				$i++;
				echo '<div id="image">Screen number: ' . $i . '
						<br /><br />
						<a onclick="window.location.href=\'delete image.php?imageId=' . $row['id'] . '\';"><img src="data:image/jpeg;base64,'.base64_encode( $row['image'] ).'"/><a>
					  </div>';
            }
			
            self::$dbh = null;
		}
		
		public static function deleteStepImage() {
			if(empty($_GET['imageId'])) {
				return;
			}
			
			self::setConnectPDO();
			
			$stat = self::$dbh->prepare("UPDATE steps_resolve_image 
										 SET    active = 'N'
										 WHERE  id = :imageId");
			$stat->bindParam(':imageId', $_GET['imageId'], PDO::PARAM_INT);
			$stat->execute();
			
			header('Location: delete image.php');
			
			self::$dbh = null;
		}
		
		public static function createAddStepCockpit(){
			self::setConnectPDO();
			
			$maxStepNumberResult = self::$dbh->prepare("SELECT Max(number) as number
														FROM   steps_resolve
														WHERE  active = 'y'
															   AND cockpit_id = :cockpitId 
															   AND error_number = :errorNumber");
			$maxStepNumberResult->bindParam(":cockpitId", $_SESSION['cockpitId'], PDO::PARAM_INT);											 
			$maxStepNumberResult->bindParam(":errorNumber", $_SESSION['errorNumber'], PDO::PARAM_INT);			
			$maxStepNumberResult->execute();
	
			$maxStepNumber = $maxStepNumberResult->fetch(PDO::FETCH_ASSOC);
														 
			echo "To add several options, separate them with ';' Example: 
			      <br />
				  first option;second option;third option 
				  <br /><br />
				  To finish the instruction, enter 0 or leave the \"Next step number\" field empty 
				  <br /><br />
				  <form action='' method='POST'>
					  <div id = 'stepNumber'>
						Number <br />
						<input type='number' name='stepNumber' min='1' value='" . ++$maxStepNumber['number'] . "'>
					  </div>
					  <div id = 'stepDescription'>
						Description <br />
						<textarea rows='4' cols='45' name='stepDescription'></textarea>
					  </div>
					  <div id = 'nextStepQuestion'>
						Split question <br />
						<textarea rows='4' cols='45' name='nextStepQustion'></textarea>
					  </div>
					  <div id = 'nextStepOption'>
						Option <br />
						<textarea rows='4' cols='45' name='nextStepOption'></textarea>
					  </div>
					  <div id = 'nextStepPossibleNumber'>
						Next step number <br />
						<textarea rows='4' cols='45' name='nextStepPossibleNumber'>" . ++$maxStepNumber['number'] . "</textarea>
					  </div>
					  <br /><br /><br />
					  <button class='button' name='step' value='1'>Add <br>step</button>
				  </form>
				  <br /><br />
				  <button class='button' onclick='window.location.href=\"../error step service.php\";'>Return to <br />error edit</button>";
				  
		   self::$dbh = null;
		}
		
		public static function addStep() {

			if(!self::checkEmptyFields()) {
				return;
			}
			
			if(!self::checkNumberOfBranches()) {
				return;
			}
			
			self::setConnectPDO();
			
			self::changeReferenceAdd();
			
			$stat = self::$dbh->prepare("INSERT INTO steps_resolve
													 (
														number,
														description,
														next_step_question,
														next_step_option,
														next_step_possible_number,
														cockpit_id,
														error_number,
														active
													 )
										 VALUE       (
														:number,
														Trim(:description),
														Trim(:nextStepQustion),
														Trim(:nextStepOption),
														Trim(:nextStepPossibleNumber),
														:cockpitId,
														:errorNumber,
														'Y'
										             )");
			$stat->bindParam('number', $_POST['stepNumber'], PDO::PARAM_INT);
			$stat->bindParam('description', $_POST['stepDescription'], PDO::PARAM_STR);
			$stat->bindParam('nextStepQustion', $_POST['nextStepQustion'], PDO::PARAM_STR);
			$stat->bindParam('nextStepOption', $_POST['nextStepOption'], PDO::PARAM_STR);
			$stat->bindParam('nextStepPossibleNumber', $_POST['nextStepPossibleNumber'], PDO::PARAM_STR);
			$stat->bindParam('cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);
			$stat->bindParam('errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);
			$stat->execute();
			
			$_SESSION['stepNumber'] = $_POST['stepNumber'];
			
			header('Location: edit step.php');
			
			self::$dbh = null;
		}
		
		private static function setConnectPDO() {
            self::$dbh = Connect::getConnect();
        }
		
		private static function changeReferenceDelete() {
			$valueChangeReference = -1;
			
			$stepDataResult = self::$dbh->prepare("SELECT number,
														  next_step_possible_number
												   FROM   steps_resolve
												   WHERE  number > :stepNumber
														  AND cockpit_id = :cockpitId
														  AND error_number = :erroNumber
														  AND active = 'Y'
												   ORDER  BY number");
			$stepDataResult->bindParam(':stepNumber', $_POST['stepNumber'], PDO::PARAM_INT);
			$stepDataResult->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);
			$stepDataResult->bindParam(':erroNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);
			$stepDataResult->execute();
			
			$nextStepPossibleNumber = "";
			
			while($stepData = $stepDataResult->fetch(PDO::FETCH_ASSOC)) {
				
				$newStepNumber = $stepData['number'] + $valueChangeReference;
				
				if(strpos($stepData['next_step_possible_number'], ';')) {
					$nextStepPossibleNumberTable = explode(';', $stepData['next_step_possible_number']);
					for($i = 0; $i<sizeof($nextStepPossibleNumberTable); $i++) {
						if($nextStepPossibleNumberTable[$i] != 0){
							$nextStepPossibleNumberTable[$i] += $valueChangeReference;
						}
						if($i != (sizeof($nextStepPossibleNumberTable) - 1)) {
							$nextStepPossibleNumber .= $nextStepPossibleNumberTable[$i] . ";";
						}
						else {
							$nextStepPossibleNumber .= $nextStepPossibleNumberTable[$i];
						}
					}
				}
				elseif($stepData['next_step_possible_number'] == 0) {
					$nextStepPossibleNumber = 0;
				}
				else {
					$nextStepPossibleNumber = $stepData['next_step_possible_number'] + $valueChangeReference;
				}
				
				$stat = self::$dbh->prepare("UPDATE steps_resolve
											 SET    number = :newStepNumber,
												    next_step_possible_number = :newNextStepPossibleNumber
											 WHERE  active = 'Y'
													AND number = :oldNumber
													AND cockpit_id = :cockpitId
													AND error_number = :errorNumber");
				$stat->bindParam(':newStepNumber', $newStepNumber, PDO::PARAM_INT);
				$stat->bindParam(':newNextStepPossibleNumber', $nextStepPossibleNumber, PDO::PARAM_STR);
				$stat->bindParam(':oldNumber',  $stepData['number'], PDO::PARAM_STR);
				$stat->bindParam(':cockpitId',  $_SESSION['cockpitId'], PDO::PARAM_INT);
				$stat->bindParam(':errorNumber',  $_SESSION['errorNumber'], PDO::PARAM_INT);
				$stat->execute();	
				
				$nextStepPossibleNumber = "";
			}
		}
		
		private static function changeReferenceAdd() {
			$isStepResult = self::$dbh->prepare("SELECT id
												 FROM   steps_resolve
												 WHERE  number = :stepNumber
														AND cockpit_id = :cockpitId
														AND error_number = :errorNumber
														AND active = 'Y'");
			$isStepResult->bindParam(':stepNumber', $_POST['stepNumber'], PDO::PARAM_INT);
			$isStepResult->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);
			$isStepResult->bindParam(':errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);
			$isStepResult->execute();
			
			if($isStepResult->rowCount() == 0) {
				return;
			}
			
			$valueChangeReference = 1;
			
			$stepDataResult = self::$dbh->prepare("SELECT number,
														  next_step_possible_number
												   FROM   steps_resolve
												   WHERE  number >= :stepNumber
														  AND cockpit_id = :cockpitId
														  AND error_number = :erroNumber
														  AND active = 'Y'
												   ORDER  BY number DESC");
			$stepDataResult->bindParam(':stepNumber', $_POST['stepNumber'], PDO::PARAM_INT);
			$stepDataResult->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);
			$stepDataResult->bindParam(':erroNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);
			$stepDataResult->execute();
			
			$nextStepPossibleNumber = "";
			
			while($stepData = $stepDataResult->fetch(PDO::FETCH_ASSOC)) {
				
				$newStepNumber = $stepData['number'] + $valueChangeReference;
				
				if(strpos($stepData['next_step_possible_number'], ';')) {
					$nextStepPossibleNumberTable = explode(';', $stepData['next_step_possible_number']);
					for($i = 0; $i<sizeof($nextStepPossibleNumberTable); $i++) {
						if($nextStepPossibleNumberTable[$i] != 0){
							$nextStepPossibleNumberTable[$i] += $valueChangeReference;
						}
						if($i != (sizeof($nextStepPossibleNumberTable) - 1)) {
							$nextStepPossibleNumber .= $nextStepPossibleNumberTable[$i] . ";";
						}
						else {
							$nextStepPossibleNumber .= $nextStepPossibleNumberTable[$i];
						}
					}
				}
				elseif($stepData['next_step_possible_number'] == 0) {
					$nextStepPossibleNumber = 0;
				}
				else {
					$nextStepPossibleNumber = $stepData['next_step_possible_number'] + $valueChangeReference;
				}
				
				$stat = self::$dbh->prepare("UPDATE steps_resolve
											 SET    number = :newStepNumber,
												    next_step_possible_number = :newNextStepPossibleNumber
											 WHERE  error_number = :errorNumber
													AND number = :oldNumber
													AND cockpit_id = :cockpitId
													AND active = 'Y'");
				$stat->bindParam(':newStepNumber', $newStepNumber, PDO::PARAM_INT);
				$stat->bindParam(':newNextStepPossibleNumber', $nextStepPossibleNumber, PDO::PARAM_STR);
				$stat->bindParam(':oldNumber',  $stepData['number'], PDO::PARAM_STR);
				$stat->bindParam(':cockpitId',  $_SESSION['cockpitId'], PDO::PARAM_INT);
				$stat->bindParam(':errorNumber',  $_SESSION['errorNumber'], PDO::PARAM_INT);
				$stat->execute();
				
				$nextStepPossibleNumber = "";
			}
		}
		
		private static function checkEmptyFields() {
			if(empty($_POST['nextStepOption'])) {
				$_POST['nextStepOption'] = 'Next';
			}
			
			if(empty($_POST['nextStepPossibleNumber'])) {
				$_POST['nextStepPossibleNumber'] = '0';
			}
			if(empty($_POST['step'])) {
				return false;
			}
			
			else if(empty($_POST['stepDescription'])) {
				echo "<script>
						alert('Description can\'t be empty');
					  </script>";
				return false;
			}
			
			return true;
		}
		
		private static function checkNumberOfBranches() {
			$nextStepPossibleOption = explode(';', $_POST['nextStepOption']);
			$nextStepPossibleNumber = explode(';', $_POST['nextStepPossibleNumber']);
			
			if(sizeof($nextStepPossibleOption) != sizeof($nextStepPossibleNumber)) {
				 echo "<script>
						  alert('Incorrect number of branches');
					   </script>";
				return false;
			}
			
			return true;
		}
	}