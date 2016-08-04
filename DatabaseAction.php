<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 31/07/16
 * Time: 14:27
 */

require_once 'Database.php';

class DatabaseAction extends Database{

    /**@var mysqli_stmt */
    private $stmt;
    private $rowCount;

    /**
     * @return int
     */
    public function getRowCount()
    {
        return $this->rowCount;
    }

    /**
     * @param int $rowCount
     */
    public function setRowCount($rowCount)
    {
        $this->rowCount = $rowCount;
    }

    public function __construct()
    {
        parent::connect();
    }

    /**
     * Prepares a query for use as a prepared query
     * @throws Exception Query preparation failure information
     */
    private function prepare($query)
    {
        if(!($this->stmt = $this->getDatabase()->prepare($query)))
            throw new Exception("Query failed to prepare ".$this->getDatabaseError());
    }

    /**
     * @param string $query
     * @param array $parameters First item in the array
     * determines the number and types of parameters
     * for example ss for 2 strings and the next 2 items contain
     * the data
     *
     * @throws Exception Array not supplied in array format or
     * Query failed to bind
     *
     * @internal param $
     */
    public function bind_param($query, $parameters = array())
    {
        if(empty($query)) throw new Exception("No query.");
        if(!is_array($parameters)) throw new Exception("Parameters not supplied in the correct format. Array expected");
        $this->prepare($query);
        if(empty($parameters)) return;

        $tmp = array();
        foreach($parameters as $key => $value) $tmp[$key] = &$parameters[$key];

        if(!call_user_func_array(array($this->stmt, 'bind_param'), $tmp)){
            print_r($this->stmt);
            throw new Exception("Parameters failed to bind.".$this->stmt->errno);
        }
    }

    /**
     * Executes the mysqli prepared statement
     * @throw Exception Query failed to execute
     */
        public function execute()
    {
        if(!($this->stmt->execute()))
            throw new Exception("Query failed to execute ".$this->stmt->error);
        $this->stmt->store_result();
    }

    /**
     * @return array A multidimensional array containing
     * rows and assoc fields
     */
    public function bind_result_fetch()
    {
        // Get metadata for field names
        $meta = $this->stmt->result_metadata();

        // This is the tricky bit dynamically creating an array of variables to use
        // to bind the results
        while ($field = $meta->fetch_field()) {
            $var = $field->name;
            $$var = null;
            $fields[$var] = &$$var;
        }

        // Bind Results
        call_user_func_array(array($this->stmt,'bind_result'),$fields);

        // Fetch Results
        $i = 0;
        while ($this->stmt->fetch()) {
            $results[$i] = array();
            foreach($fields as $k => $v)
                $results[$i][$k] = $v;
            $i++;
        }

        $this->setRowCount(count($results));
        return $results;
    }

    /**
     * @param string $query A mysql formatted query
     * @return mysqli_result The result of a mysqli query
     */
    public function query($query) {
        $q = $this->getDatabase()->query($query);// or die('Query Execution Error: ' . $query);
        $this->setRowCount($q->num_rows);
        return $q;
    }

    /**
     * @param string $query A mysql formatted query
     * @return int Number of affected rows
     */
    public function insert($query)
    {
        $this->query($query);
        return $this->getDatabase()->affected_rows;
    }

    /**
     * @param string $query A mysql formatted query
     * @return int Number of affected rows
     */
    public function delete($query)
    {
        $this->query($query);
        return $this->getDatabase()->affected_rows;
    }

    public function update($query, $params = array())
    {
        $this->prepare($query);
        $this->bind_param($params);
        $this->execute();
        return $this->stmt->affected_rows;
    }

    /**
     * @param string $query A query to be executed. The query must be fully formed.
     * @return array|null An array containing associated data from a query.
     */
    public function fetch_assoc($query) {

        $q = null;
        try{
            $result = $this->query($query);

            while($data = $result->fetch_assoc())
                $q[] = $data;
            echo "x";

        }
        catch (Exception $e)
        {
            echo $e->getMessage();

        }
        return $q;
    }

    public function cleanup()
    {
        $this->stmt->free_result();
        $this->stmt->close();
    }

} 
