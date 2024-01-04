<?php

require("classes/Database.php");

class Activity {

    public $id;
    public $name;
    public $type;
    public $responsible_staff;
    public $summary;
    public $newest_start_time;
    public $newest_end_time;
    public $old_start_time;
    public $old_end_time;

    function __construct($activity) {
        foreach($activity as $key => $value) {
            $this->$key = $value;
        }
    }

    static public function getActivity($id) {
        $conn = Database::connect();

        $sql = "SELECT a.id, a.name, a.type, a.responsible_staff, a.summary, t.room,
                          TIME_FORMAT(t.start_time, '%H:%i') AS newest_start_time,
                          TIME_FORMAT(t.end_time, '%H:%i') AS  newest_end_time
                          FROM activities AS a
                          LEFT JOIN activities_time_and_place AS t ON a.id = t.activity_id 
                          WHERE a.id = ? ORDER BY t.timestamp LIMIT 1;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();

        return new self($result);
    }
}
