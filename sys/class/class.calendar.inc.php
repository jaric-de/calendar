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
     * Индекс дня недели, с которого начинается месяц (1-7)
     *
     * @var int: день недели, с которого начинается месяц
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
        /**
         * Определить порядковый номер дня недели, когда началася месяц
         */
        $this->_startDay = $dateTimeObj->modify('first day of this month')->format('w');
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
            $sql .= " WHERE `event_id`=:id LIMIT 1";
        }
        /**
         * В противном случае зугрузить все события относяшиеся к использ. месяцу
         */
        else
        {
            $dateTimeObj = new DateTime($this->_useDate);
            $firstDayOfTheMonth = $dateTimeObj->modify('first day of this month')->format('Y-m-d');
            $LastDayOfTheMonth = $dateTimeObj->modify('last day of this month')->format('Y-m-d');
            $sql .= "WHERE `event_start`
                        BETWEEN '$firstDayOfTheMonth'
                        AND '$LastDayOfTheMonth'";
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
            $results = $stmt->fetchAll();
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

    private function _loadEventById($id) {
        if (empty($id)) {
            return NULL;
        }
        $event = $this->_loadEventData($id);
        if (isset($event[0])) {
            return new Event($event[0]);
        } else {
            return NULL;
        }
    }

    /**
     * Возвращеет HTML-разметку для отображения календаря и событий для данного месяца в зависимости от заданной даты
     */
    public function buildCalendar()
    {
        /**
         * Определить месяц календаря и созадть массив сокр. обозночений дня недели
         */
        $dateTimeObj = new DateTime($this->_useDate);
        $calMonth = $dateTimeObj->format('F Y');
        $weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        /**
         * Добавить заговок в HTML-разметку
         */
        $html = "\n\t<h2>$calMonth</h2>";
        for ($d = 0, $labels = NULL; $d < 7; ++$d) {
            $labels .= "\n\t\t<li>" . $weekdays[$d] . "</li>";
        }
        $html .= "\n\t<ul class='weekdays'>" . $labels . "\n\t</ul>";

        /**
         * Загрузить данные о событиях
         */
        $events = $this->_createEventObj();

        /**
         * Добавить HTML-разметку календаря
         */
        $html .= "\n\t<ul>"; // начать новый unsorted list
        /**
         * $i - счетчик итераций (по сути это ячейка на поле. Чаще всего одна из 42 (7*6))
         * $с - счетчик календарных дат (30 или 31. в случае с февралем - 28-29)
         * $t - текущее число месяца
         * $m - текущий номер месяца
         */
        for ($i = 1, $c = 1, $t = $dateTimeObj->format('j'), $m = $dateTimeObj->format('m'), $y = $dateTimeObj->format('Y'); $c <= $this->_daysInMonth; ++$i) {
            $class = $i <= $this->_startDay ? "fill" : NULL;
            /**
             * добавим класс today, если дата совпадает с текущей
             */
            if ($c === $t && $m === $this->_m && $y = $this->_y) {
                $class = "today";
            }

            $ls = sprintf("\n\t\t<li class = '%s'>", $class); // tag <li> start
            $le = "\n\t\t</li>"; // tag </li> end

            $eventInfo = NULL;
            /**
             * Добавить день месяца, идент. ячейку календаря
             */
            if ($this->_startDay < $i && $this->_daysInMonth >= $c) {
                /**
                 * Форматировать данные о событиях
                 */
                if (isset($events[$c])) {
                    foreach ($events[$c] as $event) {
                        $link = '<a href="view.php?event_id=' . $event->id . '">' . $event->title . '</a>';
                        $eventInfo .= "\n\t\t\t$link";
                    }
                }

                $date = sprintf("\n\t\t\t<strong>%d</strong>", $c++);
            } else {
                $date = "&nbsp;";
            }

            /**
             * Если теущий день суббота, перейти в след. ряд
             */
            $wrap = ($i !== 0 && $i % 7 === 0) ? "\n\t</ul>\n\t<ul>" : NULL;

            /**
             * Собрать разрозненые части воедино
             */
            $html .= $ls . $date . $eventInfo . $le . $wrap;
        }

        /**
         *
         */
        while ($i%7 !== 1) {
            $html .= "\n\t\t<li class = 'fill'>&nbsp;</li>";
            ++$i;
        }
        $html .= "\n\t</ul>\n\n";
        
        return $html;
    }

    public function displayEvent($id) {
        if (empty($id)) {
            return NULL;
        }
        $id = preg_replace('/[^0-9]/', '', $id);
        $event = $this->_loadEventById($id);
        $date = (new DateTime($event->start))->format('F d, Y');
        $start = (new DateTime($event->start))->format('g:ia');
        $end = (new DateTime($event->end))->format('g:ia');

        return "<h2>$event->title</h2>"
            . "\n\t<p class='dates'>$date, $start&mdash;$end</p>"
            . "\n\t<p>$event->description</p>";
    }

    public function displayForm()
    {
        $id = isset($_POST['event_id']) ? (int)$_POST['event_id'] : NULL;
        $submit = 'Create Event';
        $event = new Event([]);
        if (!empty($id)) {
            $event = $this->_loadEventById($id);
            if (!is_object($event)) {
                return NULL;
            }

            $submit = "Change event";
        }

        /**
         * Создать разметку
         */
        return <<<FORM_MARKUP
        <form action="assets/inc/process.inc.php" method="post">
            <fieldset>
                <legend>$submit</legend>
                <label for=event_title>Event name</label>
                <input type="text" name="event_title"
                    id="event_title" value="$event->title" />
                <label for=event_start>Event Start</label>
                <input type="text" name="event_start"
                    id="event_start" value="$event->start" />
                <label for=event_end>Event end</label>
                <input type="text" name="event_end"
                    id="event_end" value="$event->end" />
                <label for=event_description>Event Description</label>
                <textarea name="event_description"
                    id="event_description">$event->description
                </textarea>    
                <input type="hidden" name="event_id" value="$event->id" />
                <input type="hidden" name="token" value="$_SESSION[token]" />
                <input type="hidden" name="action" value="event_edit" />
                <input type="submit" name="event_submit" value="$submit" />
                or <a href="./">Cancel</a>
            </fieldset>
        </form>
FORM_MARKUP;
    }
}