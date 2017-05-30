<?php

/**
 * Class Admin
 *
 * Управляет выполнением администатртивных задач
 */
class Admin extends DB_Connect
{
    private $_saltLength = 7;

    public function __construct($db = NULL, $saltLength = NULL)
    {
        parent::__construct($db);

        /**
         * Если передан целочисл параметр, задать длину затравки для хеширование паролей
         */
        if (is_int($saltLength)) {
            $this->_saltLength = $saltLength;
        }
    }

    /**
     * Проверяет действительность учетных данных пользователя
     *
     * @return bool|string: TRUE в случае успешного завершения, иначе - сообщение об ошибке
     */
    public function processLoginForm()
    {
        
        if ($_POST['action'] !== 'user_login') {
            return "processLoginForm() got unsupported value of form action";
        }

        $uname = htmlentities($_POST['uname'], ENT_QUOTES);
        $pword = htmlentities($_POST['pword'], ENT_QUOTES);

        $sql = "SELECT 
                    `user_id`, `user_name`, `user_email`, `user_pass`
                FROM `users`
                WHERE
                    `user_name` = :uname
                LIMIT 1"
        ;

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':uname', $uname, PDO::PARAM_STR);
            $stmt->execute();
            $user = array_shift($stmt->fetchAll());
            $stmt->closeCursor();
        } catch (Exception $e) {
            die($e->getMessage());
        }

        // Аварийное завершение, если имя пользователя не согласуется ни с одной записью в БД
        if (!isset($user)) {
            return 'There is no any user with such name';
        }

        /**
         * получаем хэш для пароля, который ввел юзер
         */

        echo "<pre>";
        print_r($user['user_pass']);
        echo "</pre>";

        $hash = $this->_getSaltedHash($pword, $user['user_pass']);

        echo "<pre>";
        print_r($hash);
        echo "</pre>";

        if ($user['user_pass'] === $hash) {
            $_SESSION['user'] = [
                'id' => $user['user_id'],
                'name' => $user['user_name'],
                'email' => $user['user_email']
            ];
            return TRUE;
            /**
             * Аварийное завершение в случае несовпадения паролей
             */
        } else {
            return "Incorrect name or password were given";
        }
    }

    public function processLogout()
    {
        if ($_POST['action'] !== 'user_logout') {
            return 'Incorrect name of action was given into the processLogout() function';
        }

        /**
         * удалить массив юзер из текущего сеанса
         */
        session_destroy();
        return TRUE;
    }

    /**
     * Генерирует хеш-код с затравкой
     *
     * @param $string
     * @param null $salt
     * @return string
     */
    private function _getSaltedHash($string, $salt = NULL)
    {
        if ($salt === NULL) {
            $salt = substr(md5(time()), 0, $this->_saltLength);
        } else {
            $salt = substr($salt, 0, $this->_saltLength);
        }

        return $salt . sha1($salt . $string);
    }


    //----------------//----------------//----------------// TESTING //----------------//----------------//----------------//
    public function testSaltedHash($string, $salt = NULL)
    {
        return $this->_getSaltedHash($string, $salt);
    }
}