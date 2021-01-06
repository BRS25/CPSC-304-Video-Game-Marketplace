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

  <a href="<?php echo 'main.php'?>">
    <?php echo "Home";?>
</a>




<?php

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("orcl", "orcl", "dbhost.ugrad.cs.ubc.ca:1522/ug");
//$db_conn = OCILogon("ora_k0a0b", "a27221143", "dbhost.ugrad.cs.ubc.ca:1
$username = "";
/*if (isset($_COOKIE["username"]))
{*/
    $username = $_COOKIE["username"];
//}


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

if ($db_conn) {  

  
  $sql = "SELECT Account.username, Account.password, Account.phone, creditcard.card, Account.recovery
          FROM Account
          LEFT JOIN creditcard ON Account.username=creditcard.username
          WHERE Account.username = '".$username."'";
         // WHERE Account.username = '".$username."'";
         $result = executePlainSQL($sql);
         $row = OCI_Fetch_Array($result, OCI_BOTH);
         $username= $row[0];
         $password = $row[1];
         $phone = $row[2];
         $creditcard = $row[3];
         $email = $row[4];
// define variables and set to empty values





}
 $nameErr = $emailErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (empty($_POST["name"])) {
    $nameErr = "Name is required";
  } else {
    $name = test_input($_POST["name"]);
    // check if name only contains letters and whitespace
    if (!preg_match("/^[a-zA-Z ]*$/",$name)) {
      $nameErr = "Only letters and white space allowed"; 
    }
  }
  
  if (empty($_POST["email"])) {
    $emailErr = "Email is required";
  } else {
    $email = test_input($_POST["email"]);
    // check if e-mail address is well-formed
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $emailErr = "Invalid email format"; 
    }
  }
    
  if (empty($_POST["creditcard"])) {
    $creditcard = "";
  } else {
    $creditcard = test_input($_POST["creditcard"]);
  
  }

  if (empty($_POST["phone"])) {
    $phone = "";
  } else {
    $phone = test_input($_POST["phone"]);
  }
  if (empty($_POST["password"])) {
    $password = "";
  } else {
    $password = test_input($_POST["password"]);
  }
  if (!empty($_POST['submit'] && $_POST['submit'] == 'Save')) {
        
        $username = test_input($username);
       $sql_account = 
       "UPDATE Account
       SET phone = '".$phone."', recovery = '".$email."'
       WHERE username = '".$username."'";

       $sql_creditcard =
         "UPDATE creditcard
          SET card = ".$creditcard."
          WHERE username = '".$username."'";
       executePlainSQL($sql_account);
      // executePlainSQL($sql_creditcard);
       OCICommit($db_conn);
        
    }

  
 

 
 }



function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}


?>


<h2>Edit User</h2>
<div style="text-align: center;">.
<!--<p><span style="display:none" class="error">* required field.</span></p> -->
<form id="form" method="post"   >  
  <label>Name:</label>
  <br>
   <input etype="text" id="username" name="username" value="<?php echo test_input($username);?>" readonly>
  <br>
  <label>Password:</label>
  <br>
  <div style="display:inline-block;";>
    <input type="password" id="password" name="password" value="<?php echo test_input($password)?>"><!-- <br><button style="background:#f8f8f8">Change Password</button><br><br><br> -->
    <button onclick="togglePassword()">Show Password</button>
  </div>
  <br>
  <br>
  <label>E-mail:</label>

  <br>
  <br>
  <input type="text" id="email" name="email" value="<?php echo test_input($email);?>" >
  <span class="error"> <?php echo $emailErr;?></span>
  <br>
  <label>Phone:</label>
   <input type="text" id = "phone" name="phone" value="<?php echo test_input($phone);?>" >
  <br>

  <br>
  <label>Credit Card:</label>
  <br>
   <input type="number" readonly id="creditcard" name="creditcard" value="<?php echo test_input($creditcard)?>" >
  <br>
  <br>
  <input style="border:solid;font-weight: bold;" type="submit" name="submit" onclick= "" value="Save">
</form>
</div>

<?php




      


?>

<script>
  function printThis(s){
    console.log(s);
  }
  function userInfoChanged(){
    document.getElementsByName('submit')[0].value = "Save";
  }
  function togglePassword() {
    var input = document.getElementsByName('password')[0];
    if (input.type == "password") {
      input.type = "text";
    } else {
      input.type = "password";
    }
  }
</script>

</body>
</html>