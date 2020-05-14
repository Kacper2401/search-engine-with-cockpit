<?php
    class SearchCommands {
        private static $dbh;
		private static $PRECISION = 70;

        public static function displayDescriptionStep() {
            self::setConnectPDO();

            self::checkStepNumber();

            $stepResult = self::$dbh->prepare("SELECT description, 
                                                      next_step_option, 
                                                      next_step_possible_number, 
                                                      next_step_question
                                               FROM   steps_resolve
                                               WHERE  number = :stepNumber
                                                      AND cockpit_id = :cockpitId
                                                      AND error_number = :errorNumber
													  AND active = 'Y'");
            $stepResult->bindParam(':stepNumber', $_SESSION['stepNumber'], PDO::PARAM_INT);
            $stepResult->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);
            $stepResult->bindParam(':errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);
            $stepResult->execute();

            $row = $stepResult->fetch(PDO::FETCH_ASSOC);

            echo "<b>Step ". $_SESSION['stepNumber'] . "</b>
				  <br /><br />";

            echo nl2br($row['description']) . "<br />
				 <br />";

            self::createStepButtons($row);

            $stepResult->closeCursor();

            self::displayImage();
        }

        public static function displayStepList() {
            self::setConnectPDO();

            echo "<b>Step list</b>
					<ul>";

            $stepListResult = self::$dbh->prepare("SELECT COUNT(number) AS numberSteps
                                                   FROM   steps_resolve
                                                   WHERE  error_number = :errorNumber
                                                          AND cockpit_id = :cockpitId
														  AND active = 'Y'");
            $stepListResult->bindParam(':errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);
            $stepListResult->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);
            $stepListResult->execute();

            $row = $stepListResult->fetch(PDO::FETCH_ASSOC);

            for($i = 1; $i <= $row['numberSteps']; $i++ ) {
                if($i == $_SESSION['stepNumber']) {
                    echo "<br />
						  <li>
							<b>Step" . $i . "</b>
						  </li>
						  <br />";
                }
                else {
                    echo "<li>
							<a href='steps.php?changeStep=" . $i . "'>Step" . $i . "</a>
						  </li>";
                }
            }
            echo "</ul>";

            self::$dbh = null;
        }

        public static function displayDecisionTree() {
            self::setConnectPDO();

            $decisionTreeResult = self::$dbh->prepare("SELECT decision_tree
                                                       FROM   decision_tree_images
                                                       WHERE  error_number = :errorNumber
                                                              AND cockpit_id = :cockpitId
															  AND active = 'Y'");
            $decisionTreeResult->bindParam(':errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);
            $decisionTreeResult->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);
            $decisionTreeResult->execute();

            $row = $decisionTreeResult->fetch(PDO::FETCH_ASSOC);
            if(!empty($row)) {
                echo '<img src="data:image/jpeg;base64,'.base64_encode( $row['decision_tree'] ).'"/>';
            }

            self::$dbh = null;
        }


        public static function searchError() {
            self::setConnectPDO();
			
			if(self::checkIfHeChoseError()) {
				return;
			}

            self::checkCockpitId();

            $error = self::checkErrorName();

			$errorPart = self::prepareErrorToSearch($error);

			$errorIdFromCockpitResult = self::$dbh->prepare("SELECT id
														     FROM   errors_list
														     WHERE  cockpit_id = :cockpitId
																    AND active = 'Y'");
			$errorIdFromCockpitResult->bindParam(':cockpitId', $_POST['cockpitId'], PDO::PARAM_INT);
			$errorIdFromCockpitResult->execute();
			
			$rowCount = $errorIdFromCockpitResult->rowCount();
			for($i = 0; $i < $rowCount; $i++) {
				$tmp = $errorIdFromCockpitResult->fetch(PDO::FETCH_ASSOC);
				$errorMatching[$i][0] = $tmp['id'];
				$errorMatching[$i][1] = 0;
			}
			
			for($i = 0; $i < sizeof($errorPart); $i++) {
				$searchResult = self::$dbh->prepare("SELECT id
													 FROM   errors_list
													 WHERE  active = 'Y'
															AND cockpit_id = :cockpitId
															AND name like :errorPart");
				$searchResult->bindParam(':cockpitId', $_POST['cockpitId'], PDO::PARAM_INT);
				$searchResult->bindParam(':errorPart', $errorPart[$i], PDO::PARAM_STR);
				$searchResult->execute();

				while($row = $searchResult->fetch(PDO::FETCH_ASSOC)) {
					for($j = 0; $j < sizeof($errorMatching); $j++) {
						if($errorMatching[$j][0] == $row['id']) {
							$errorMatching[$j][1]++;
						}
					}
				}
			}
			$idList = "";	
			$tmp = 0;
			
			if(empty($errorMatching)) {
				$errorMatching[0][1] = -1;
			}
			
			for($i = 0; $i < sizeof($errorMatching); $i++) {
				if((($errorMatching[$i][1] / sizeof($errorPart)) * 100) >= self::$PRECISION) {
					if($tmp == 0) {
						$idList .= "id = " . $errorMatching[$i][0];
						$tmp++;
					}
					else {
						$idList .= " OR id = " . $errorMatching[$i][0];
					}
				}
			}
			$idList .= ")";
			if(strcmp($idList,")") == 0) {
				echo "<b><a href='index.php'>Error not found... click me to go to main page!</a></b>";
				self::$dbh = null;
				return;
			}

			$errorNameResult = self::$dbh->prepare("SELECT id, 
														   name,
														   number,
														   cockpit_id
													FROM   errors_list 
													WHERE  active = 'Y'
														   AND (". $idList);
			$errorNameResult->execute();
            self::initializationManual($errorNameResult);

            self::$dbh = null;
        }

        private static function setConnectPDO() {
            self::$dbh = Connect::getConnect();
        }
		
		private static function checkIfHeChoseError() {
			if(empty($_GET['errorId'])) {
				return false;
			}
			
			self::setConnectPDO();
			
			$errorNameResult = self::$dbh->prepare("SELECT id, 
														   name,
														   number,
														   cockpit_id
													FROM   errors_list 
													WHERE  id = :errorId");
			$errorNameResult->bindParam(':errorId', $_GET['errorId'], PDO::PARAM_INT);			
			$errorNameResult->execute();			
			self::initializationManual($errorNameResult);
			
			self::$dbh = null;
			 
			return true;
		}
		
		private static function initializationManual($errorNameResult) {
            if($errorNameResult->rowCount() == 1) {
                $row = $errorNameResult->fetch(PDO::FETCH_ASSOC);
                session_start();
                $_SESSION['errorName'] = "<b>".$row['name']. "</b>";
                $_SESSION['errorNumber'] = $row['number'];
                $_SESSION['cockpitId'] = $row['cockpit_id'];
                $_SESSION['stepCounter'] = 1;
                $_SESSION['stepNumber'] = 1;
                self::$dbh = null;
                header("Location: steps.php");
            }
            else {
                self::displayPossibleErrors($errorNameResult);
            }
        }
		
		private static function displayPossibleErrors($errorNameResult) {
            echo "<div id='message'>
					Too many match errors. Possible errors: 
				  </div>
				  <br />
                  <ul id ='test'>";
            while($row = $errorNameResult->fetch(PDO::FETCH_ASSOC)) {
                $error = strtolower(str_replace(' ', '', $row['name']));
                echo "<li>
						<a href='verification.php?errorId= " . $row['id'] . "'> Error name: " . $row['name'] . '<br /><br />' . "</a>
					  </li>";
            }
            echo "</ul>";
        }
	
        private static function checkCockpitId() {
            if(!isset($_POST['cockpitId'])) {
                $_POST['cockpitId'] = 1;
            }
        }

        private static function checkErrorName() {
            if(!empty($_POST['error'])) {
                return $_POST['error'];
            }
            else {
                return "Empty error";
            }
        }
		
		private static function prepareErrorToSearch($error) {
			$errorPart = explode(" ", trim($error));
            for($i = 0; $i < sizeof($errorPart); $i++) {
                $errorPart[$i] = "%" . strtolower($errorPart[$i]) . "%";
            }
			
			return $errorPart;
		}

		private static function checkStepNumber() {
            if(isset($_GET['changeStep'])) {
                $_SESSION['stepNumber'] = $_GET['changeStep'];
            }

            if(isset($_POST['returnButton'])) {
                header('Location: index.php');
            }

            if(isset($_POST['nextStepNumber'])) {
                $_SESSION['stepNumber'] = $_POST['nextStepNumber'];
            }
            elseif(isset($_POST['previousStep']))  {
                $_SESSION['stepNumber'] = $_POST['previousStep'];
            }

            $_SESSION['stepCounter']++;

            if(isset($_POST['previousStep'])) {
                $_SESSION['stepCounter'] -= 2;
            }

            $_SESSION['doneStepList'][$_SESSION['stepCounter']] = $_SESSION['stepNumber'];
        }

        private static function displayImage() {
            $stepResult = self::$dbh->prepare("SELECT id,
                                                      image,
                                                      steps_resolve_id
                                               FROM   steps_resolve_image
                                               WHERE  steps_resolve_id = (SELECT id
                                                                          FROM   steps_resolve
                                                                          WHERE  number = :stepNumber
                                                                                 AND cockpit_id = :cockpitId
                                                                                 AND error_number = :errorNumber
																				 AND active = 'Y')
													  AND active = 'Y'");
            $stepResult->bindParam(':stepNumber', $_SESSION['stepNumber'], PDO::PARAM_INT);
            $stepResult->bindParam(':cockpitId', $_SESSION['cockpitId'], PDO::PARAM_INT);
            $stepResult->bindParam(':errorNumber', $_SESSION['errorNumber'], PDO::PARAM_INT);
            $stepResult->execute();

            while($row = $stepResult->fetch(PDO::FETCH_ASSOC)) {
                echo '<br /><br /><img src="data:image/jpeg;base64,'.base64_encode( $row['image'] ).'"/>';
            }

            self::$dbh = null;
        }

        private static function createStepButtons($row) {
            echo "<form action='steps.php' method='post'>";
            if($row['next_step_possible_number'] != 0) {
                if(empty($row['next_step_question'])) {
                    echo "<button name='nextStepNumber' class='button' value=" . $row['next_step_possible_number'] . ">" . $row['next_step_option'] . "</button>";
                }
                else {
                    $optionsName = explode(";",$row['next_step_option']);
                    $optionsNumber = explode(";",$row['next_step_possible_number']);

                    echo $row['next_step_question']."<br /><br />";
                    for($i = 0; $i < sizeof($optionsName); $i++) {
                        echo "<button name='nextStepNumber' class='button' value=" . $optionsNumber[$i] . ">" . $optionsName[$i] . "</button>&nbsp;&nbsp;&nbsp;&nbsp;";
                    }
                }
            }
            elseif($row['next_step_possible_number'] == 0) {
                echo "<button name='returnButton' class='button' value='0'> Return </button>";
            }
            if($_SESSION['stepCounter'] > 2) {
                echo "<br /><br />
					  <button name='previousStep' class='button' value=" . $_SESSION['doneStepList'][$_SESSION['stepCounter'] - 1] . ">Previous</button>";
            }
            echo "</form>";
        }
    }