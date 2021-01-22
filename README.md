Description:

This code creates a table in a PostgreSQL database, reads a CSV file, parses data from csv file, and insert cleaned data into the table.

Test:
1. php user_upload.php --help //print all the help information
2. php user_upload.php --create_table -h localhost -u postgres -p 123456 //create users table in PostgreSQL database
3. php user_upload.php --file users.csv --dry_run -h localhost -u postgres -p 123456 //read file, clean data, but NOT insert data into database
4. php user_upload.php --file users.csv -h localhost -u postgres -p 123456 //read file, clean data, and insert into database

Note:

Database name: since database name is not provided from command line, here let's assume database name is "postgres", which is set as a private variable. 
To test and run the code correctly in different environment, this may be changed accordingly.
