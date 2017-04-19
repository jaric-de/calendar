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
    
    private function _loadEventData($id = NULL)
    {
        $sql = "SELECT
                      `event_id`, `event_title`, `event_desc`, `event_start`, `event_end`
                FROM `events`";

        /**
         * загрузить событие по event_id, если передан параметр id
         */
        if (!empty($id)) {
            $sql .= "WHERE `event_id` =:id LIMIT 1";
        }
        /**
         * В противном случае зугрузить все события относяшиеся к использ. месяцу
         */
        else
        {
            $dateTimeObj = new DateTime($this->_useDate);
            $firstDayOfTheMonth = $dateTimeObj->modify('first day of this month')->format('Y-m-d H:i:s');
            $LastDayOfTheMonth = $dateTimeObj->modify('last day of this month')->format('Y-m-d H:i:s');
            $sql .= "WHERE `event_start`
                        BETWEEN `$firstDayOfTheMonth`
                        AND `$LastDayOfTheMonth`";
        }

        try
        {
            $stmt = $this->db->prepare($sql);
            /**
             * Привязать параметр, если был передан идентификатор
             */
            if (!empty($id)) {
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            }
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            return $results;
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    private function _createEventObj()
    {
        /**
         * Загрузить массив событий
         */
        $arr = $this->_loadEventData();
        $events = [];
        foreach ($arr as $event) {
            $day = (new DateTime($event['event_start']))->format('j');
            try
            {
                $events[$day][] = new Event($event);
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
        return $events;
    }
}