<?php
    ini_set('display_errors', 1);
    // $entityBody = json_decode(file_get_contents('php://input'));
    //this is the basic way of getting a database handler from PDO, PHP's built in quasi-ORM
    $dbhandle = new PDO("sqlite:scrabble.sqlite") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
 
    // this is a sample query which gets some data, the order by part shuffles the results
    // the limit 0, 10 takes the first 10 results.
    // you might want to consider taking more results, implementing "pagination", 
    // ordering by rank, etc.
    $characters = 'BCDFGHJKLMNPQRSTVWXYZ';
    $vowels = 'AEIOU';
    $results = [];
    $letterCombos = [];
    $gameLetters = ''; 
    // generate random strings
    //generate constants
    for($x = 0; $x < 6; $x++){
        $index = rand(0,strlen($characters)-1);
        $letter = substr($characters,$index,1);
        $gameLetters = $gameLetters."".$letter;
    }
    for($x = 0; $x < 2; $x++){
        $index = rand(0,strlen($vowels)-1);
        $letter = substr($vowels,$index,1);
        $gameLetters = $gameLetters."".$letter;
    }

    // lets get that bingo
    $query = "SELECT rack, words FROM racks WHERE length=8 order by random() limit 1";
    $statement = $dbhandle->prepare($query);
    $statement->execute();
    $result = $statement -> fetchAll();
    foreach( $result as $row ) {
        $myrack = $row["rack"];
    }
    $racks = [];
    for($i = 0; $i < pow(2, strlen($myrack)); $i++){
        $ans = "";
        for($j = 0; $j < strlen($myrack); $j++){
            //if the jth digit of i is 1 then include letter
            if (($i >> $j) % 2) {
            $ans .= $myrack[$j];
            }
        }
        if (strlen($ans) > 1){
            $racks[] = $ans;	
        }
    }
    $racks = array_unique($racks);
    // print_r($racks);
    


    foreach ($racks as &$value){
        
        $query = "SELECT rack, weight, words FROM racks WHERE rack=\"$value\"";
        //this next line could actually be used to provide user_given input to the query to 
        //avoid SQL injection attacks
        $statement = $dbhandle->prepare($query);
        $statement->execute();
        
        //The results of the query are typically many rows of data
        //there are several ways of getting the data out, iterating row by row,
        //I chose to get associative arrays inside of a big array
        //this will naturally create a pleasant array of JSON data when I echo in a couple lines
        $results = array_merge($statement->fetchAll(PDO::FETCH_ASSOC),$results);
    }
    
    //this part is perhaps overkill but I wanted to set the HTTP headers and status code
    //making to this line means everything was great with this request
    header('HTTP/1.1 200 OK');
    //this lets the browser know to expect json
    header('Content-Type: application/json');
    //this creates json and gives it back to the browser
    // echo json_encode($results);
    echo json_encode($results);
    // header('Content-Type: application/text');

    // echo($gameLetters);

?>