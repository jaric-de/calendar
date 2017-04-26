<?php

/**
 * Class Event
 * Хранит информацию о событии
 */
class Event
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;
    /**
     * @var string: время начала события
     */
    public $start;
    /**
     * @var string: время окончания события
     */
    public $end;

    public function __construct($event)
    {
        if (is_array($event)) {
            $this->id = isset($event['event_id']) ? $event['event_id'] : NULL;
            $this->title = isset($event['event_title']) ? $event['event_title'] : NULL;
            $this->description = isset($event['event_desc']) ? $event['event_desc'] : NULL;
            $this->start = isset($event['event_start']) ? $event['event_start'] : NULL;
            $this->end = isset($event['event_end']) ? $event['event_end'] : NULL;
        } else {
            throw new Exception("The event data were not presented");
        }
    }
}