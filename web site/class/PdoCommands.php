<?php
    class PdoCommands {
        private static $dbh;

        public static function createIndexButtons() {
            self::setConnectPDO();

            $cockpitNameResult = self::$dbh->query("SELECT id, 
                                                           shortcut 
                                                    FROM   cockpit_list
                                                    WHERE  active = 'Y' 
                                                    ORDER  BY id");

			$firstCockpit = true;
            while ($row = $cockpitNameResult->fetch(PDO::FETCH_ASSOC)) {
				if($firstCockpit) {
					echo 	"<label class='container'>" . $row['shortcut'] . "
								<input type='checkbox' name='cockpitId' value='" . $row['id'] . "' checked>
								<span class='checkmark'></span>
							 </label>";
					$firstCockpit = false;
				}
				else {
					echo 	"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					         <label class='container'>" . $row['shortcut'] . "
								<input type='checkbox' name='cockpitId' value='" . $row['id'] . "'>
								<span class='checkmark'></span>
							 </label>";
				}
            }

            self::$dbh = null;
        }

        public static function downloadFile() {
            self::setConnectPDO();

            if(!isset($_GET['fileNumber'])) {
                return;
            }
            $fileNumber = $_GET['fileNumber'];

            $addiionalMaterialResult = self::$dbh->prepare("SELECT file,
																   name,
																   type 
														    FROM   additional_materials 
															WHERE  id = :fileNumber
																   AND active = 'Y'");
            $addiionalMaterialResult->bindParam(':fileNumber', $fileNumber, PDO::PARAM_INT);
            $addiionalMaterialResult->execute();

            $fileData = $addiionalMaterialResult->fetch(PDO::FETCH_ASSOC);
            if(!empty($fileData)) {
                header('Content-Description: File Transfer');
                header('Content-Type: '. $fileData['content_type'].'; charset=utf-8"');
                header('Content-Disposition: attachment; filename="' . $fileData['name'] . "." . $fileData['type'] . '"');
                header('Expires: 0');
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header('Cache-Control: private', false);
                header('Pragma: public');
                ob_clean();
                flush();
                print $fileData['file'];
            }

            header("Locaction: additional materials.php");
			
            self::$dbh = null;
        }

        public static function displayAdditionalFileList() {
            self::setConnectPDO();

            $fileNameResult = self::$dbh->query("SELECT id, 
                                                        name 
                                                 FROM   additional_materials 
                                                 WHERE  active = 'Y'
                                                 ORDER  BY name");

            echo "<ul id='file'>";
            while ($row = $fileNameResult->fetch(PDO::FETCH_ASSOC)) {
                echo "<li>
						<a href='additional materials.php?fileNumber=".$row['id']."'><img src='../images/word.png'>".$row['name']."</a>
					  </li>
					  <br /><br />";
            }
            echo "</ul>";

            echo "<div id='warming'> 
					Warming 
				  </div> 
				  <br /> 
				  Files should be started by microsoft word. After open you should recovery file (Option yes in program)";

            $dbh = null;
        }

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
						Cockpit: " . $row['shortcut'] . '<br /><br />' . "
					  </li>";
                echo "<ul id='error'>";
                while ($row = $errorNameResult->fetch(PDO::FETCH_ASSOC)) {
                    echo "<li>
							<a href='verification.php?errorId=" . $row['id'] . "'> Error name: " . $row['name'] . '<br /><br />' . "</a>
						  </li>";
                }
                echo "</ul>
						<br />";

                $errorNameResult->closeCursor();
            }
            echo "</ul>";

            self::$dbh = null;
        }

        private static function setConnectPDO() {
            self::$dbh = Connect::getConnect();
        }
    }