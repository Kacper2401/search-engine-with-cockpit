<?php
    include_once('class/PdoCommands.php');
    include_once("../Connect.php");
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <link rel="stylesheet" href="../style/template web site.css" />
        <link rel="stylesheet" href="../style/web site/additional materials.css" />
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" />
        <link rel="stylesheet" href="../style/additional materials.css">
        <meta name="author" content="Kacper Przybylski" />
        <title>Manual</title>
    </head>
    <body>
    <header>
        <div id="menu">
            <table id="menu">
                <td id="manual">
                    <a href="index.php"> Search </a>
                </td>
                <td id="manual">
                    <a href="error list.php"> Error list </a>
                </td>
                <td>
                    <a href="additional materials.php"> Additional materials </a>
                </td>
            </table>
        </div>
        <hr />
    </header>
    <main>
        <?php
            try {
                PdoCommands::displayAdditionalFileList();
                PdoCommands::downloadFile();
            }
            catch(PDOException $e) {
                echo "<a href='index.php'>Something went wrong... click me to go to main page!</a>";
            }
        ?>
    </main>
    <footer>
        <hr />
        © 2020
    </footer>
    </body>
</html>