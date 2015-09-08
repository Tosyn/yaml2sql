<?php 

require_once('./vendor/autoload.php');

use Symfony\Component\Yaml\Yaml;

/**
 * Convert test data in yaml format to sql
 */
class Yaml2Sql  {
		
	/**
	 * @var $output_file - where to write sql file
	 */
	protected $output_file;
	
	/**
	 * @var $yaml_file
	 */
	protected $yaml_file;
	
	/**
	 * 
	 */
	function __construct($yaml_file) {
		$this->output_file 	= '/tmp/'.pathinfo($yaml_file, PATHINFO_FILENAME) .'.sql';
		
		if (!is_readable($yaml_file))
			throw new Exception('file does not exist or not readable');
		else 
			$this->yaml_file 	= $yaml_file;
	}
	
	
	/**
	 * Generate sql statement from yaml file data
	 * @return path to output file
	 */
	public function generateSql()
	{
		// Get data
		$data = $this-> getData();
		
		if ($data) {
			// open output file for writing
			$sql_file = fopen($this->output_file, "w") or die("Unable to write to /tmp/ directory!");
			
			// start transaction
			fwrite($sql_file, "BEGIN; \n");
			
			// generate sql statements
			foreach ($data as $table => $rows) {
				if ($rows) {
					$table_name = $this->deCamelise($table);
					$sql_statements = $this->getSqlStatements($table_name, $rows);
					
					// write sql statement to output file
					if ($sql_statements) {
						foreach ($sql_statements as $query)
							fwrite($sql_file, $query ."; \n");
					}
				}
			}	
			
			// commit transaction
			fwrite($sql_file, "COMMIT; \n");
			
			// close output file
			fclose($sql_file);		
		}
		
		return PHP_EOL .'Generated sql file can be found here: ' .$this->output_file . PHP_EOL;
	}
	
	
	/**
	 * Parse yaml file
	 * 
	 * @return data[array]
	 */
	public function getData()
	{
		$data = Yaml::parse(file_get_contents($this->yaml_file));
		
		return $data;
	}
	
	/**
	 * Convert CamelCase to snake_case
	 * @param CamelCase[string]
	 * 
	 * @return snake_case[string]
	 */
	public function deCamelise($input) {
	  preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
	  $ret = $matches[0];
	  foreach ($ret as &$match) {
	    $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
	  }
	  return implode('_', $ret);
	}	
	
	
	/**
	 * 
	 * Generate statements
	 * @param $table_name[string]
	 * @param $rows[array]
	 * 
	 * @return statements[array]
	 */
	public function getSqlStatements($table_name, array $rows)
	{
		$sql_statements = [];
		
		foreach ($rows as $row) {
			$fields = "";
			$values = "";
			$cols = array_keys($row);
			end($cols);
			$last_index = key($cols);
			
			
			for ($i=0; $i < count($cols); $i++) {
				$delimeter =  $i === $last_index ? '' : ',';
				$fields .= $cols[$i] . $delimeter;
				$values .= "'".$row[$cols[$i]]."'".$delimeter;
			}
			
			$sql_statements[] = "INSERT INTO {$table_name} ({$fields}) VALUES({$values})";
		}
		
		return $sql_statements;
	}
	
}

