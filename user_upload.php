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
			// Only get argument starting with "-"
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
		if ($this->argumentsArr["help"] = true) {
			$this->help();
		}

		if ($this->argumentsArr["create_table"] = true) {
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

	private function createTable() {

	}
}

$users = new Users();
$users->run($argc, $argv);

?>