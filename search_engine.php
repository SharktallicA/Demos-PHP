<?php

    /*
        PDO and class secured search
        example by Khalid Ali, 2018-01-03
        https://sharktallica.wordpress.com

        Code based on the search engine from
        http://pathto2265.com/

        Legend:
        (R.*) - things you need to replace
    */

    class SearchEngine
    {
        private $dbHandle; //database handle
        private $statement; //prepared statement
        private $query; //query string

        function __construct()
        {
            //usage: SearchEngine constructor

            try
            {
                $this->dbHandle = new PDO("mysql:dbname=(R.dbname);host=(R.hosturl)", "(R.DBusername)", "(R.DBpassword)"); //initialise database handle
                $this->dbHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch (PDOException $e) //catches any errors during $dbhandle initialisation
            {
                echo "SearchEngine Error: " . $e->getMessage();
            }
        }

        function cleanString($string)
        {
            //usage: takes a string and cleans it of special characters
            //parametres: ($string) string to clean
            return preg_replace('/[^0-9a-zA-Z\s]/', '', $string);
        }

        function processInput($input)
        {
            //usage: takes a search string and breaks it down into tags, then returns the result as an array
            //parametres: ($input) search input string
            return explode(" ", $input);
        }

        function createStandardSearch($class, $col, $term)
        {
            //usage: creates a standard search of the database (ideal for one-term searches)
            //parametres: ($class) table to query, ($col) column to query, ($term) term to look for
            
            $this->query = "SELECT * FROM ".$class." WHERE ".$col." LIKE :term"; //assemble query string
            $this->statement = $this->dbHandle->prepare($this->query); //create prepared statement
            $this->statement->bindValue(':term', '%' . $term . '%', PDO::PARAM_INT); //bind variable $term safely to SQL query
            $this->statement->execute(); //run prepared statement
        }

        function createTagSearch($class, $terms)
        {
            //usage: creates a tag-based search (ideal for multiple termed searches)
            //parametres: ($class) table to query, ($term) array of terms to look for
            //note: the format of DB table column containing tags is expected to be comma-separated strings (varchar)
            
             $this->query = "SELECT * FROM ".$class." WHERE";
             $sqlTags = array();

            foreach($terms as $term)
            {
                $sqlTags[] = "(R.tagcolumn) LIKE '%".addslashes($term)."%'";
            }
            
            $this->query .= " ".join(' OR ',$sqlTags).";"; //assemble query string
            $this->statement = $this->dbHandle->prepare($this->query); //create prepared statement
            $this->statement->execute(); //run prepared statement
        }

        function countResult()
        {
            //usage: returns query row count
            return $this->statement->rowCount();
        }

        function getResult()
        {
            //usage: returns query result when one result is expected
            return $this->statement->fetch(PDO::FETCH_ASSOC);
        }

        function getResults()
        {
            //usage: returns query result when more than one result is expected
            return $this->statement->fetchAll();
        }
    }

?>