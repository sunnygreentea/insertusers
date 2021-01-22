<?php

//------------------------------------------------------------------------
//  This code is executed from the command line.
// The script create a table in a PostgreSQL database, reads a CSV file, parses data and insert data into the table.
//------------------------------------------------------------------------

class Users {

	// NOTE: since database name is not provided from command line, here let's assume database name is "postgres"
	// To run the code correctly, this may be changed
	private $db_name = "postgres";

	// All argument from command line
	private $argumentsArr = array(
		"file" 			=> false,
		"create_table" 	=> false,
		"dry_run" 		=> false,
		"u" 			=> false,
		"p" 			=> false,
		"h" 			=> false,
		"help" 			=> false
	);



	//-----------------------------------------------------------------
	// Construct
	//-----------------------------------------------------------------
	public function __construct () {

	}

	//-----------------------------------------------------------------
	// Run 
	//-----------------------------------------------------------------
	public function run ($argc, $argv) {
		$this->buildArgumentsArr ($argc, $argv);
		//print_r($this->argumentsArr);
		$this->doit();
	}

	//-----------------------------------------------------------------
	// Build arguments array
	//-----------------------------------------------------------------
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
				default:
					echo "Error with options\n";
					$this->argumentsArr["help"] = true;
					break;
			}
		}
	}

	//-----------------------------------------------------------------
	// DO different work according to different input arguments
	//-----------------------------------------------------------------
	private function doit () {

		// Help --help
		// If the input argument contains "--help", show help list info
		if ($this->argumentsArr["help"] == true) {
			$this->help();
			exit(1);
		}

		// Create table --create_table
		// If the input argument contains "--create_table", call function "createTable" to "create" or "drop and create" users table
		// After creating table, check number of users inserted to make sure table is "freshly" created
		if ($this->argumentsArr["create_table"] == true) {
			if ($this->argumentsArr["h"]!=false && $this->argumentsArr["u"]!=false && $this->argumentsArr["p"]!=false){
				$this->createTable();
				exit(1);
			}else {
				echo "please input the arguments [-h], [-u], [-p]\n";
				$this->help();
				exit(1);
			}
		}	

		// Dry run --dry_run
		// If the input argument contains "--dry_run", do everything except creating table and inserting users into database.
		// What should do: read csv file and parse data, check data to make data rady to insert
		if ($this->argumentsArr["dry_run"] == true) {
			if($this->argumentsArr["file"]) {
				// Passing argument "false" to indicate NOT insert into database
				$this->parseAndInsert(false);
				exit(1);
			}
			else {
				echo "Please input the arguments [--file]\n";
				$this->help();
				exit(1);
			}
		}

		// File --file -h host -u username -p password
		// If the input argument contains "--file", parse data from csv file and insert into database
		if($this->argumentsArr["file"] && $this->argumentsArr["dry_run"] == false) {
			// Passing argument "true" to indicate YES insert into database
			$this->parseAndInsert(true);
			exit(1);
		}
		
	}

	//-----------------------------------------------------------------
	// Parse data and insert into database
	//-----------------------------------------------------------------
	private function parseAndInsert ($doInsert) {
		// Show user data
		$usersArr = $this->parseData();
		
		// if found optoins -h, -u, -p, then insert data into db
		if ($this->argumentsArr["h"]!=false && $this->argumentsArr["u"]!=false  && $this->argumentsArr["p"]!=false){
			// insert
			$this->insertUsers($usersArr, $doInsert);
			// check
			$this->getUsers();
		}
		// else show help info
		else {
			echo "Please input the arguments [-h], [-u], [-p]\n";
			$this->help();
		}
	}

	//-----------------------------------------------------------------
	// Output help list info
	//-----------------------------------------------------------------
	private function help () {
		echo "--file [csv file name] – this is the name of the CSV to be parsed.\n";
		echo "--create_table – this will cause the PostgreSQL users table to be built (and no further actionw will be taken).\n";
		echo "--dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered.\n";
		echo "-u – PostgreSQL username.\n";
		echo "-p – PostgreSQL password.\n";
		echo "-h – PostgreSQL host.\n";
		echo "--help – which will output the above list of directives with details.\n";
		
	}


	//-----------------------------------------------------------------
	// Parse data from csv
	//-----------------------------------------------------------------
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
		    echo "\nData extracted from file: ".$this->argumentsArr["file"]."\n";
		    print_r($usersArr);
		    echo "\n";
		    return $usersArr;
		}
	}

	//-----------------------------------------------------------------
	// Create or recreate table
	//-----------------------------------------------------------------
	private function createTable() {
		$conn = $this->connectDB();		
		
		// Create table
		$sql = "DROP TABLE IF EXISTS users;
				CREATE TABLE users (
	            id SERIAL PRIMARY KEY,
	            name VARCHAR(100) NOT NULL,
	            surname VARCHAR(100) NOT NULL,
	            email VARCHAR(255) UNIQUE);";
	    $result = pg_query($conn, $sql);

	    if($result) { 
	    	echo "Table users created.\n";
	    	echo "sql : ".$sql ."\n";
	    	$this->getUsers();
	    }
	    else {
	    	echo "Failed to create table.\n";
	    }
	}


	//-----------------------------------------------------------------
	// Connect to database
	//-----------------------------------------------------------------
	private function connectDB () {
		
		$db_host = $this->argumentsArr["h"];
		$db_user = $this->argumentsArr["u"];
		$db_password = $this->argumentsArr["p"];
		
		$db_handle = pg_pconnect("host=$db_host dbname=$this->db_name user=$db_user password=$db_password");

		if ($db_handle) {
			return $db_handle;
		} else {
			echo "Failed to connect database.\n";
		}
	}

	//-----------------------------------------------------------------
	// Insert users
	// argument: $doInsert: if true then do insert, else do not insert
	//-----------------------------------------------------------------
	private function insertUsers ($usersArr, $doInsert) {
		$conn = $this->connectDB();	
		
		foreach($usersArr as $user) {
			$name = ucfirst($this->cleanText($user["name"]));
			$surname = ucfirst($this->cleanText($user["surname"]));
			$email =  $this->cleanText($user["email"]);
			
			if($this->validateEmail($email)) {
				echo "Email valid. Ready to insert: ".$name.",".$surname.",",$email."\n";
				if ($doInsert == true){
					$sql = "INSERT INTO users (name, surname, email) VALUES ('$name','$surname','$email');";
					//echo "sql ".$sql."\n";
					$result = pg_query($conn, $sql);
					if (!$result){
						echo "Insert failed: ".$name.",".$surname.",".$email."\n\n";
					}
					else {
						echo "Inserted: ".$name.",".$surname.",".$email."\n\n";
					}
				}
			}
			else {
				echo "Email NOT valid:".$name.",".$surname.",",$email."\n\n";
			}	
		}
	}

	//-----------------------------------------------------------------
	// Get users from database
	//-----------------------------------------------------------------
	private function getUsers() {
		
		$conn = $this->connectDB();	
		$sql = "SELECT * FROM users";
		$result = pg_query($conn, $sql);
		if(pg_num_rows($result)>0) {
			echo "\n\nAll users in the table:\n"; 
			while($row = pg_fetch_array($result)){
				$k = $row['name']." ".$row['surname']." ".$row['email']; 
				echo $k."\n"; 
			}
		}
		else {
			echo "No users yet.\n";
		}
		
	}

	//-----------------------------------------------------------------
	// Clean text such as name, surname, and email
	//-----------------------------------------------------------------
	private function cleanText ($text) {
		return strtolower(trim(str_replace("'","''", $text)));
	}

	//-----------------------------------------------------------------
	// Validte email
	//-----------------------------------------------------------------
	private function validateEmail ($email) {
		if (preg_match('/^([0-9a-zA-Z]([-!\.\w]*[0-9a-zA-Z][\'\!]*)*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$/i', $email)) 
    		return true;
		else
			return false;
	}
}


//-----------------------------------------------------------------
$users = new Users();
$users->run($argc, $argv);

?>