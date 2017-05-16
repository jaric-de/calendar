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
}