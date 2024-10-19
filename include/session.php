<?php

class Session {

    private $table = "sessionData";
    private $dbLoc;

    public function __construct($_dbLoc){
        $this->dbLoc = $_dbLoc;
        // Set handler to overide SESSION
        // session_set_save_handler(
        // "_open",
        // "_close",
        // "_read",
        // "_write",
        // "_destroy",
        // "_gc"
        // );
        session_set_save_handler(
            array($this, "_open"),
            array($this, "_close"),
            array($this, "_read"),
            array($this, "_write"),
            array($this, "_destroy"),
            array($this, "_gc")
        );

        // Start the session
        session_start();
    }

    /**
     * Open
     */
    public function _open(){
        return true;
    }

    /**
     * Close
     */
    public function _close(){
        return true;
    }

    /**
     * Read
     */
    public function _read($id){
        //global $db;
        $stmt = $this->dbLoc->prepare("SELECT data FROM ".$this->table." WHERE id = ?");
        if($stmt->execute(array($id))) {
            $row = $stmt->fetch();
            return ($row["data"]!=null) ? $row["data"] : '';
        }
        else {
            return '';
        }
    }

    /**
     * Write
     */
    public function _write($id, $data)
    {
        //global $db;
        $unixtime = time();
        $stmt = $this->dbLoc->prepare("REPLACE INTO " . $this->table . " VALUES (?, ?, ?)");
        if ($stmt->execute(array($id, $data, $unixtime))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Destroy
     */
    public function _destroy($id){
        //global $db;
        $stmt = $this->dbLoc->prepare("DELETE FROM ".$this->table." WHERE id = ?");
        if($stmt->execute(array($id))) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Garbage Collection
     */
    public function _gc($max){
        //global $db;
        return true;
    }

}

