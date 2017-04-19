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
            $this->id = $event['event_id'];
            $this->title = $event['event_title'];
            $this->description = $event['event_desc'];
            $this->start = $event['event_start'];
            $this->end = $event['event_end'];
        } else {
            throw new Exception("The event data were not presented");
        }
    }
}