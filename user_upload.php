<?php

class Users {

	private $argumentsArr = array(
		"file" 			=> false,
		"create_table" 	=> false,
		"dry_run" 		=> false,
		"u" 			=> false,
		"p" 			=> false,
		"h" 			=> false,
		"help" 			=> false
	);

	public function __construct () {

	}

	public function run ($argc, $argv) {
		$this->buildArgumentsArr ($argc, $argv);
		print_r($this->argumentsArr);
		$this->doit();
	}

	private function buildArgumentsArr ($argc, $argv) {
		for ($i=0; $i < $argc; $i++) {
			
			$argument = $argv[$i];
			if (substr($argument,0,1)!="-")
				continue;

			switch ($argument) {
				case "--file":
					$this->argumentsArr["file"] = $argv[$i+1];
					break;
				case "--create_table":
					$this->argumentsArr["create_table"] = true;
					break;
				case "--dry_run":
					$this->argumentsArr["dry_run"] = true;
					break;
				case "-u":
					$this->argumentsArr["u"] = $argv[$i+1];
					break;
				case "-p":
					$this->argumentsArr["p"] = $argv[$i+1];
					break;
				case "-h":
					$this->argumentsArr["h"] = $argv[$i+1];
					break;
				case "--help":
					$this->argumentsArr["help"] = true;
					break;
			}
		}
	}

	private function doit () {
		if ($this->argumentsArr["help"] == true) {
			$this->help();
		}

		if($this->argumentsArr["file"]) {
			$this->parseData();
		}

		if ($this->argumentsArr["create_table"] == true) {
			$this->createTable();
		}	
	}

	private function help () {
		echo "--file [csv file name] – this is the name of the CSV to be parsed.\n";
		echo "--create_table – this will cause the PostgreSQL users table to be built (and no further actionw will be taken).\n";
		echo "--dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered.\n";
		echo "-u – PostgreSQL username.\n";
		echo "-p – PostgreSQL password.\n";
		echo "-h – PostgreSQL host.\n";
		echo "--help – which will output the above list of directives with details.\n";
		exit(-1);
	}

	private function parseData () {

		$usersArr = array();
		$fields = array(); 
		
		$file = fopen($this->argumentsArr["file"], "r");
	    if ($file) {
	    	$i = 0;
		    while ($row = fgetcsv($file)) {
		        if (empty($fields)) {
		            $fields = $row;
		            continue;
		        }
		        
		        if(count($row)==3) { // must have name, surname, email
		        	foreach ($row as $k=>$value) {
			            $usersArr[$i][trim($fields[$k])] = $value;
			        }
			        $i++;
		        }
		        
		    }
		    if (!feof($file)) {
		        echo "Error: unexpected fgets() fail\n";
		    }
		    fclose($file);

		    var_dump($usersArr);
		    return $usersArr;
		}
	}

	private function createTable() {
		$conn = $this->connectDB();		
		$query = "DROP TABLE IF EXISTS users;
				CREATE TABLE users (
	            id SERIAL PRIMARY KEY,
	            name VARCHAR(100) NOT NULL,
	            surname VARCHAR(100) NOT NULL,
	            email VARCHAR(255) UNIQUE);";
	    pg_query($conn, $query);
	}



	private function connectDB () {
		/*
		$db_host = $this->argumentsArray["h"];
		$db_user = $this->argumentsArray["u"];
		$db_password = $this->argumentsArray["p"];
		*/

		$db_host = "localhost";
		$db_name = "postgres";
		$db_user = "postgres";
		$db_password = 123456;
		
		$db_handle = pg_pconnect("host=$db_host dbname=$db_name user=$db_user password=$db_password");

		if ($db_handle) {
			echo 'Connection attempt succeeded.';
			return $db_handle;
		} else {
			echo 'Connection attempt failed.';
		}


	}
}

$users = new Users();
$users->run($argc, $argv);

?>