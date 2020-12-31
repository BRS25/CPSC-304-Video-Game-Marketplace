<!DOCTYPE HTML>  
<html>
<head>
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<style>
.error {color: #FF0000;}
</style>
<link rel="stylesheet" href="main_style.css">
</head>
<body>  



<h2 id="username_header" onclick="goToEditUser()"></h2>

<script>
  function goToEditUser(){
    window.location.href = "edit_user.php"
  }
  function setHeader(name){
    document.getElementById("username_header").textContent = name;
  }
</script>

<?php

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_z4m0b", "a26370149", "dbhost.ugrad.cs.ubc.ca:1522/ug");


if (isset($_COOKIE["username"]))
{
    $username = $_COOKIE["username"];
}
echo "<script> setHeader('".$username."');</script>";

function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
  //echo "<br>running ".$cmdstr."<br>";
  global $db_conn, $success;
  $statement = OCIParse($db_conn, $cmdstr); //There is a set of comments at the end of the file that describe some of the OCI specific functions and how they work

  if (!$statement) {
    echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
    $e = OCI_Error($db_conn); // For OCIParse errors pass the       
    // connection handle
    echo htmlentities($e['message']);
    $success = False;
  }

  $r = OCIExecute($statement, OCI_DEFAULT);
  if (!$r) {
    echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
    $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
    echo htmlentities($e['message']);
    $success = False;
  } else {

  }
  return $statement;

}

function executeBoundSQL($cmdstr, $list) {
  /* Sometimes a same statement will be excuted for severl times, only
   the value of variables need to be changed.
   In this case you don't need to create the statement several times; 
   using bind variables can make the statement be shared and just 
   parsed once. This is also very useful in protecting against SQL injection. See example code below for       how this functions is used */

  global $db_conn, $success;
  $statement = OCIParse($db_conn, $cmdstr);

  if (!$statement) {
    echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
    $e = OCI_Error($db_conn);
    echo htmlentities($e['message']);
    $success = False;
  }

  foreach ($list as $tuple) {
    foreach ($tuple as $bind => $val) {
      //echo $val;
      //echo "<br>".$bind."<br>";
      OCIBindByName($statement, $bind, $val);
      unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype

    }
    $r = OCIExecute($statement, OCI_DEFAULT);
    if (!$r) {
      echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
      $e = OCI_Error($statement); // For OCIExecute errors pass the statementhandle
      echo htmlentities($e['message']);
      echo "<br>";
      $success = False;
    }
  }

}
function printResult($result){

  //echo "<br>(for testing only)Got data from Account<br>";
  echo "<table>";
  echo "<tr><th>Username</th><th>Listingid</th>
  <th>Active</th><th>Selling</th>
  <th>Title</th><th>Price</th>
  <th>Location</th><th>Date Posted</th><th>Contact</th></tr>";

  while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
    
    echo "<tr><td>" . $row[0] . "</td><td>" . $row[1]
     . "</td><td>" . $row[2] . "</td><td>" . $row[3]
      . "</td><td>" . $row[4] . "</td><td>" . $row[5]
       . "</td><td>" . $row[6] . "</td><td>" . $row[7] 
        . "</td><td> <a href = 'mailto:"  . $row[8]
     . "'> E-Mail</a> </td></tr> <br>";
  }

  echo "</table>";
}


  if ($db_conn) {  
    echo "<br>Here your previous listings:<br>"; 
  
      $result = executePlainSQL("SELECT COUNT(*) From Listings_Post 
                 INNER JOIN Account ON Listings_Post.username = Account.username where Listings_Post.username = '" .$username ."'");
      $row = OCI_Fetch_Array($result, OCI_BOTH);
      if($row[0] > 0) //TODO: Fix the condition for this!
      {
        //echo $row[0];
        $result = executePlainSQL("
                 SELECT Listings_Post.username, Listings_Post.listingid, Listings_Post.active, Listings_Post.selling, Listings_Post.title, Listings_Post.price, Listings_Post.location, Listings_Post.dateposted, Account.recovery
                 From Listings_Post 
                 INNER JOIN Account ON Listings_Post.username = Account.username
                 WHERE Listings_Post.username = '".$username."'");
          printResult($result);
        }
        else
        {
          echo "Unfortunately, no active listings were found.";
        }
  }
 




      

?>
<div>
<input style="border:solid;font-weight: bold;" type="submit" name="submit" value="Edit User Info" onclick="goToEditUser()">  
</div>

</body>
</html>