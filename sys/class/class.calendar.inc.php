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

        /**
         * отобразить опции администрирования
         */
        $admin = $this->_adminGeneralOptions();
        
        return $html . $admin;
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

        $admin = $this->_adminEntryOptions($id);

        return "<h2>$event->title</h2>"
            . "\n\t<p class='dates'>$date, $start&mdash;$end</p>"
            . "\n\t<p>$event->description</p>$admin";
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

    public function processForm()
    {
        if ($_POST['action'] !== 'event_edit') {
            return 'processForm method incorrect using';
        }

        $title = htmlentities($_POST['event_title'], ENT_QUOTES);
        $desc = htmlentities($_POST['event_description'], ENT_QUOTES);
        $start = htmlentities($_POST['event_start'], ENT_QUOTES);
        $end = htmlentities($_POST['event_end'], ENT_QUOTES);

        /**
         * Если ID не передан, значит создаем новое событие
         */
        if (empty($_POST['event_id'])) {
            $sql = "INSERT INTO `events`
                      (`event_title`, `event_desc`, `event_start`, `event_end`)
                    VALUES (:title, :description, :start, :end)";
        } else {
            $id = (int)$_POST['event_id']; // POST всегда возвращает данные в виде строки. В целях безопасности желательно привести к int
            $sql = "UPDATE `events`
                    SET `event_title` = :title,
                        `event_desc` = :description,
                        `event_start` = :start,
                        `event_end` = :end
                    WHERE `event_id` = $id";
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(":title", $title, PDO::PARAM_STR);
            $stmt->bindParam(":description", $desc, PDO::PARAM_STR);
            $stmt->bindParam(":start", $start, PDO::PARAM_STR);
            $stmt->bindParam(":end", $end, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();
            return TRUE;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Этот скрипт запускается в двух случаях. 1) Когда мы нажимаем на кнопку DELETE 2) Когда мы в форме подтверждения нажимаем на любую из кнопок
     *
     * @param $id
     * @return null|string|void
     */
    public function confirmDelete($id)
    {
        if (empty($id)) {
            return NULL;
        }

        $id = preg_replace('/[^0-9]/', '', $id);

        if (isset($_POST['confirm_delete']) && $_POST['token'] === $_SESSION['token']) { // мы попадаем в этот if только если мы работает с формой подтверждением
            if ($_POST['confirm_delete'] === "Yes, delete") { // нажали `Yes, Delete`
                $sql = "DELETE FROM `events` WHERE `event_id`=:id LIMIT 1";
                try {
                    $stmt = $this->db->prepare($sql);
                    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                    $stmt->execute();
                    $stmt->closeCursor();
                    header('Location: ./');
                } catch (Exception $e) {
                    return $e->getMessage();
                }
            }
            else // нажали `No, it was a joke`
            {
                header("Location: ./");
                return;
            }
        }

        $event = $this->_loadEventById($id);

        if (!is_object($event)) {
            header("Location: ./");
        }

        return <<<CONFIRM_DELETE
<form action="confirmdelete.php" method="post">
    <h2>Are you sure you want to delete the event "$event->title"</h2>
    <p><strong>Deleted event is impossible to reestablish</strong></p>
    <p>
        <input type="submit" name="confirm_delete" value="Yes, delete" />
        <input type="submit" name="confirm_delete" value="No, it was a joke!" />
        <input type="hidden" name="event_id" value="$event->id" />
        <input type="hidden" name="token" value="$_SESSION[token]" />
    </p>
</form>
CONFIRM_DELETE;
    }

    /**
     * Генерирует разметку для отображения административных ссылок
     */
    private function _adminGeneralOptions()
    {
        if (isset($_SESSION['user'])) {
            return <<<ADMIN_OPTIONS
<a href="admin.php" class="admin">+ Add new event</a>
<form action="assets/inc/process.inc.php" method="post">
    <div>
        <input type="submit" value="Logout" class="admin" />
        <input type="hidden" name="token" value="$_SESSION[token]" />
        <input type="hidden" name="action" value="user_logout" />
    </div>
</form>
ADMIN_OPTIONS;
        } else {
            return <<<ADMIN_OPTIONS
            <a href="login.php">Log In</a>
ADMIN_OPTIONS;
        }
    }


    /**
     * Генерирует разметку для отображения административных ссылок редактирования и удаления события для конкретного события по id
     *
     * @param int $id: идентифекатор события
     * @return string
     */
    private function _adminEntryOptions($id)
    {
        if (isset($_SESSION['user'])) {
            return <<<ADMIN_OPTIONS
<div class="admin-options">
    <form action="admin.php" method="post">
        <p>
            <input type="submit" name="edit_event" value="Edit event" />
            <input type="hidden" name="event_id" value="$id" />
        </p>
    </form>
     <form action="confirmdelete.php" method="post">
        <p>
            <input type="submit" name="delete_event" value="Delete event" />
            <input type="hidden" name="event_id" value="$id" />
        </p>
    </form>
</div>
ADMIN_OPTIONS;
        } else {
            return NULL;
        }
    }
}