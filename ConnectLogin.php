<?php
    class ConnectLogin {
        private static $server = "mysql";
        private static $host = "localhost";
        private static $base = "login";
        private static $login = "root";
        private static $password = "";
        private static $option = array(
											PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAMES 'UTF8'",
											PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION,
											PDO::ATTR_EMULATE_PREPARES, false
										);

        public static function getConnect() {
           return new PDO(self::getHandle(), self::$login, self::$password, self::$option);
        }

        private static function getHandle() {
            return self::$server . ":host=" . self::$host . ";dbname=" . self::$base;
        }
    }