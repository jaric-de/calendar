<?php

/**
 * Class Calendar
 * Обеспечивает создание календаря и манипулирование событиями
 */
class Calendar extends DB_Connect
{
    /**
     * Дата, на основании которой должен строиться календарь
     *
     * @var string: дата, выбранная для построение календаря
     */
    private $_useDate;

    /**
     * Месяц, для которого строится календарь
     *
     * @var int: выбранный месяц
     */
    private $_m;

    /**
     * Год, из которого выюирается начальный день месяца
     *
     * @var: выбранный год
     */
    private $_y;

    /**
     * @var int: количество дней в месяце
     */
    private $_daysInMonth;

    /**
     * Индекс дня недели, с которого начинается месяц (0-6)
     *
     * @var int: день недели,  с которого начинается месяц
     */
    private $_startDay;

    /**
     * Calendar constructor.
     * @param null $dbo: объект базы данных
     * @param null $useDate: дата, выбранная для построение календаря
     */
    public function __construct($dbo = NULL, $useDate = NULL)
    {
        /**
         * Вызвать конструктор родит. класса для проверки существ. объекта базы данных
         */
        parent::__construct($dbo);

        /**
         * Cобрать и сохранить информацию, относ. к месяцу
         */
        $dateTimeObj = is_null($useDate) ? new DateTime() : new DateTime($useDate);
        if (isset($useDate)) {
            $this->_useDate = $useDate;
        } else {
            $this->_useDate = $dateTimeObj->format('Y-m-d H:i:s');
        }
        $this->_m = $dateTimeObj->format('m');
        $this->_y = $dateTimeObj->format('Y');
        /**
         * Определить количество дней, содерж. в месяце
         */
        $this->_daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->_m, $this->_y);
        $this->_startDay = $dateTimeObj->format('w');
    }
}