<?php
include_once '../sys/config/db-cred.inc.php';
class DB_Connect {
    /**
     * @var object: db object
     */
    protected $db;

    /**
     * DB_Connect constructor.
     * Проверить наличие объекта БД, а в случае его отсутствия - создать новый
     *
     * @param null $db
     */
    protected function __construct($db = NULL) {
        if (is_object($db)) {
            $this->db = $db;
        } else {
            $dsn = "mysql:host=". DB_HOST .";dbname=". DB_NAME .";charset=" . DB_CHARSET;
            try 
            {
                $this->db = new PDO($dsn, DB_USER, DB_PASS, $GLOBALS['pdoOptions']);
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
    }
}