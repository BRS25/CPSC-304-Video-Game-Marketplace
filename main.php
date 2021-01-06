<html>
<head>
	<link rel="stylesheet" href="main_style.css">
</head>
<label>Welcome</label> 
<?php 

    $username = $_COOKIE["username"];
    //echo $username;
    //echo "Cookie: " . $_COOKIE["username"];
	

	//getcookie("user");//$_COOKIE["username"];
	//echo $username;
	//echo "!";
	//	a href = 'hello.php?id="
	//<a href="/company_details.php?id=<?php echo $companyId; 

?>
<!--Link to user's page-->
<a href="<?php echo 'view_user.php?username=' . $username; ?>">
    <?php echo $username . '!';?>
</a>

<a href="<?php echo 'login.php'?>">
    <?php echo "logout";?>
</a>


<br>
<!--<input type="submit" name = "account" value = "Profile">-->
<body>
	<form method="POST" action="main.php" >
	<label><p>Search for a listing:</p></label> <br>
	<!-- <input type="text" name="searchTerm">-->
	<input type="submit" name = "search" value = "Search">
	<!--Query for listings-->

	<label><p>Create a Listing:</p></label>
	<input type="submit" name = "buy" value = "Buy">
	<input type="submit" name = "sell" value = "Sell">
	<!--Query for listings--> 

	<label><p>Your listings:</p></label>
	<!--Query for listings made by this user-->
	<input type="submit" name = "show" value = "Show Listings">
	<!--<button type="button"
	onclick="document.getElementById('demo').innerHTML = 'HelloWorld'">
	Test.</button>
	<input type="submit" name="init" value="RESET for testing" >
	-->

	<p id="demo"></p>

</form>
<?php

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("orcl", "orcl", "dbhost.ugrad.cs.ubc.ca:1522/ug");

/*$username = "";
if (!isset($_COOKIE["username"]))
{
   	$username = $_COOKIE["username"];
}
*/
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

function printResultOld($result) { //prints results from a select statement
	//echo "<br>(for testing only)Got data from Account<br>";
	echo "<table>";
	echo "<tr><th>username</th><th>listingid</th>
	<th>active</th><th>selling</th>
	<th>title</th><th>price</th>
	<th>location</th><th>dateposted</th><th>view</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		echo "<tr><td>" . $row[0] . "</td><td>" . $row[1]
		 . "</td><td>" . $row[2] . "</td><td>" . $row[3]
		  . "</td><td>" . $row[4] . "</td><td>" . $row[5]
		   . "</td><td>" . $row[6] . "</td><td>" . $row[7] 
		    . "</td><td> <a href = 'hello.php?id="  . $row[1]
		 . "'> Link</a> </td></tr> <br>"; //or just use "echo $row[0]"
		//echo '<a href = "hello.php"> Link</a>';

	}
	echo "</table>";

}

function printResult($result){

  //echo "<br>(for testing only)Got data from Account<br>";
  echo "<table>";
  echo "<tr><th>Username</th><th>Listingid</th>
  <th>Active</th><th>Selling</th>
  <th>Title</th><th>Price</th>
  <th>Location</th><th>Date Posted</th>
  <th>Company</th><th>Platform</th><th>MSRP</th>
  <th>Contact</th></tr>";

  while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
    
    echo "<tr><td>" . $row[0] . "</td><td>" . $row[1]
     . "</td><td>" . $row[2] . "</td><td>" . $row[3]
      . "</td><td>" . $row[4] . "</td><td>" . $row[5]
       . "</td><td>" . $row[6] . "</td><td>" . $row[7]
        . "</td><td>" . $row[8] . "</td><td>" . $row[9] . "</td><td>" . $row[10] 
        . "</td><td> <a href = 'mailto:"  . $row[11]
     . "'> E-Mail</a> </td></tr> <br>";
  }

  echo "</table>";
}

			


// Connect Oracle...
if ($db_conn) 
{	 
	if (array_key_exists('account', $_POST)) 
	{
		echo "you just clicked account";
		//Replace this with the user account link
		$link = "location:view_user.php?username=" + $username; 
		header("location:view_user.php?username=" + $username);
	}
	if (array_key_exists('search', $_POST)) 
	{
		echo "you just clicked search";
		//Replace this with the search page link
		header("location:search.php");
	}
	else if (array_key_exists('buy', $_POST)) 
	{
		echo "you just clicked buy";
		//Replace this with the create buy listing link
		setcookie("username", $username);
		header("location:buy.php");
	}
	else if (array_key_exists('sell', $_POST)) 
	{
		echo "you just clicked sell";
		//Replace this with the create sell listing link
		setcookie("username", $username);
		header("location:sell.php");
	}
	else if (array_key_exists('show', $_POST)) 
	{
		echo "<br>Here are the active listings:<br>";	
			$result = executePlainSQL("SELECT COUNT(*) from Listings_Post lp where lp.Active = 'true'");
			$row = OCI_Fetch_Array($result, OCI_BOTH);
			if($row[0] > 0) //TODO: Fix the condition for this!
			{

				//$result = executePlainSQL("SELECT * from Listings_Post lp where lp.Active = 'true' ORDER BY DATEPOSTED DESC");
				$sql = "SELECT DISTINCT lp.username, lp.listingid, lp.active, 
				lp.selling, lp.title, lp.price, lp.location, lp.dateposted,
				i.companyname, i.platform, i.msrp, a.recovery
				FROM Listings_Post lp, Item i, Account a 
				WHERE lp.listingid = i.listingid AND lp.username = a.username";
				$result = executePlainSQL($sql);
			//$row = OCI_Fetch_Array($result, OCI_BOTH);
			//if($row[0]!=NULL) //TODO: Fix the condition for this!
			//{
		    	printResult($result);
		    }
		    else
		    {
		    	echo "Unfortunately, no active listings were found.";
		    }
	}
	else if (array_key_exists('init', $_POST)) 
	{
		//For testing purposes only.
		echo "you just clicked reset";				
		// executePlainSQL("Drop table account_user");
		// executePlainSQL("Drop table listings_post");
		// executePlainSQL("Drop table account");

		// executePlainSQL("CREATE TABLE account (username CHAR(30),
		// 	Password CHAR(30),
		// 	Recovery CHAR(30),
		// 	member_since DATE,
		// 	phone CHAR(30),
		// 	PRIMARY KEY (username))");				

		// executePlainSQL("CREATE TABLE Account_User(
		//     username CHAR(30),
		// 	wallet_amount INTEGER,
		// 	Rating INTEGER,
		// 	PRIMARY KEY (username),
		// 	FOREIGN KEY (username)
		// 		REFERENCES Account(username))");	

		// executePlainSQL("CREATE TABLE Listings_Post(
		//     username CHAR(30) not NULL,
		//     listingID CHAR(30),
		//     Active CHAR(5),
		//     Selling CHAR(5),
		//     Title CHAR(30),
		// 	Price INTEGER,
		// 	Location CHAR(30),
		// 	DatePosted DATE,
		// 	PRIMARY KEY (listingID),
		// 	FOREIGN KEY (username)
		// 		REFERENCES Account(username))");

		// executePlainSQL("insert into account values ('user1', 'abcd','lol@gmail.com','9999-12-31','911')");
		// executePlainSQL("insert into account_user values ('user1', 100000,-9)");	

		// executePlainSQL("INSERT into listings_post values ('user1', 0,'true','true', 'Some game for sale!', 30, 'My backyard', null)");
	}
}

?>

</body>
</html>