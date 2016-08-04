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

    public function __construct()
    {
        parent::connect();
    }

    /**
     * Prepares a query for use as a prepared query
     *
     * @throws Exception Query preparation failure information
     */
    private function prepare($query)
    {
        if(!($this->stmt = $this->getDatabase()->prepare($query)))
            throw new Exception("Query failed to prepare ".$this->getDatabaseError());
    }

    /**
     * Prepares a mysqli formatted query for execution
     *
     * @param string $query A mysql formatted query
     * @param array $parameters A mixed array containing fields for binding
     * to the mysqli query
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
		
		$parameters = $this->buildArray($parameters);
		
        foreach($parameters as $key => $value) $tmp[$key] = &$parameters[$key];

        if(!call_user_func_array(array($this->stmt, 'bind_param'), $tmp)){
            //print_r($this->stmt);
			
            throw new Exception("Parameters failed to bind.".$this->stmt->errno);
        }
    }

    /**
     * Gets the type of input and returns as a single character
     * or false
     *
     * @param object|bool|float $type Any string, number, float or even object
     * @return bool|string A character or false
     */
    private function sortType($type)
	{
		$str = gettype($type);
		switch($str)
		{
			case 'double': return 'd';
			case 'integer': return 'i';
			
			case 'float':
			case 'string': return 's';
			
			case 'NULL':
            case 'bool':
			case 'object': return false;
		}
	}

    /**
     * Builds an array from a mixed array and sets the first entry
     * as a single character to represent each type of item in the
     * array
     *
     * @param array $arr A mixed array containing strings, bool, objects, integers etc.
     * @return array An array containing one new entry detailing the type of items
     * contained within the array
     *
     * @throws Exception If an item in the inputted array is invalid
     */
    private function buildArray($arr = array())
	{
		$char = array();
		
		foreach($arr as $item)
		{
			$x = $this->sortType($item);
			$char[] = $x;
			if(!$x) throw new Exception("An item in the array was not valid.");
		}
		
		array_unshift($arr, implode('',$char));
		return $arr;
		
	}

    /**
     * Executes a mysqli prepared statement
     *
     * @throw Exception Query failed to execute
     */
    public function execute()
    {
        if(!($this->stmt->execute()))
            throw new Exception("Query failed to execute ".$this->stmt->error);
        $this->stmt->store_result();
    }

    /**
     * Use after calling bind_param($query, $params) and execute()
     * Places results from a query into an associative array
     *
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
     * Run a non prepared query
     *
     * @param string $query A mysql formatted query
     * @return mysqli_result The result of a mysqli query
     */
    public function query($query) {
        $q = $this->getDatabase()->query($query);// or die('Query Execution Error: ' . $query);
        $this->setRowCount($q->num_rows);
        return $q;
    }

    /**
     * Inserts a row into the database
     *
     * @param string $query A mysql formatted query
     * @param array $params An array of parameters for a
     * prepared query
     *
     * @return int The number of affected rows by the statement
     */
    public function insert($query, $params = array())
    {
        return $this->runQuery($query, $params);
    }

    /**
     * Deletes a row from a mysql delete statement
     *
     * @param string $query A mysql formatted query
     * @param array $params An array of parameters for a
     * prepared query
     *
     * @return int The number of affected rows by the statement
     */
    public function delete($query, $params = array())
    {
        return $this->runQuery($query, $params);
    }

    /**
     * Updates a table from a mysql update statement
     *
     * @param string $query A mysql formatted query
     * @param array $params An array of parameters for a
     * prepared query
     *
     * @return int The number of affected rows by the statement
     */
    public function update($query, $params = array())
    {
        return $this->runQuery($query, $params);
    }

    private function runQuery($query, $params = array())
    {
        $this->bind_param($query, $params);
        $this->execute();
        return $this->stmt->affected_rows;
    }

    /**
     * Use with query() to return an array of data from the query
     * submitted assuming the query has data to return else returns
     * null
     *
     * @param string $query A query to be executed. The query must be fully formed.
     * @return array|null An array containing associated data from a query.
     */
    public function fetch_assoc($query) {

        $q = null;
        try{
            $result = $this->query($query);

            while($data = $result->fetch_assoc())
                $q[] = $data;

        }
        catch (Exception $e)
        {
            echo $e->getMessage();

        }
        return $q;
    }

    /**
     * Frees any stored data and closes the result
     */
    public function cleanup()
    {
        $this->stmt->free_result();
        $this->stmt->close();
    }

    /**
     * @return int The number of rows returned
     */
    public function getRowCount()
    {
        return $this->rowCount;
    }

    /**
     * @param int $rowCount The number of rows the last query generated
     */
    public function setRowCount($rowCount)
    {
        $this->rowCount = $rowCount;
    }
} 



//
// An example of use
//

require_once 'db/DatabaseAction.php';

$db = new DatabaseAction();

try{

    echo"<pre>";
    //$db->setQuery();
    $db->bind_param("SELECT * FROM userTest WHERE id=?", array(1));
    $db->execute();
    print_r($db->bind_result_fetch());
    $db->cleanup();
    echo "</pre>";
}
catch (Exception $e)
{
    echo $e->getMessage();
}
