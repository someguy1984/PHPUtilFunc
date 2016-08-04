<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 21/07/16
 * Time: 11:45
 */

class Database {
    /** @var MySQLi $db */
    private $db;

    /**
     * Can only be called by another database class.
     * Uses config.ini to determine username, password and database
     * Connects to a MySQL database using MySQLi
     */
    protected function connect()
    {
        $config = parse_ini_file("db/config.ini");

        $this->db = new MySQLi();
        $this->db->connect($config['host'], $config['username'], $config['password'], $config['database']);
    }

    /**
     * Returns the database object to database classes
     * @return MySQLi A MySQLi object
     */
    protected function getDatabase(){ return $this->db; }

    /**
     * Returns any error the database has thrown
     * @return string A MySQLi database error description
     */
    public function getDatabaseError() { return $this->db->error; }

    /**
     * Returns any error number the database has thrown
     * @return string A MySQLi database error number
     */
    public function getDatabaseErrorNo(){ return $this->db->errno; }
}



    $db->setQuery("SELECT * FROM userTest");
    $db->bind_param();
    $db->execute();
    print_r($db->bind_result_fetch());
    $db->cleanup();
