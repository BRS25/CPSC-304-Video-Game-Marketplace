<!DOCTYPE HTML>
<html> 

<head>
<link rel="stylesheet" href="main_style.css">
</head>


<a href="<?php echo 'login.php'?>">
    <?php echo "Logout";?>
</a>


<body>
		<?php 
$login = $_COOKIE["username"];
?>

	<label><p>Welcome, Admin 	<?php 
$login = $_COOKIE["username"];
echo $login;
?>
</p></label>
<br><br>
<label><p>Admin functions:</p></label>


<form method="POST" action="admin.php" >
<label><p>Price lower bound:</p></label> <input type="number" name="price">

<br>
<input type="submit" name="divide" value="find the user that posted all the listings priced over the lower bound" >
</form>



<form method="POST" action="admin.php" >
<label><p>Remove User account:</p></label> <input type="text" name="user">
<br>
<input type="submit" name="list" value="list all user" >
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="deleteuser" value="Remove user by username (cascade)" >
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" name="deletecc" value="Remove CreditCard information by username(no cascade)" >

<br><br><br><br>
</form>






<form method="POST" action="admin.php" >
<label><p>Remove PostId:</p></label> <input type="text" name="post">

<br>
<input type="submit" name="deletepost" value="Remove post by postid (cascade)" >
</form>

<div id= "a">
<form method="POST" action="admin.php#a" >
<label>username:</label> <input type="text" name="user">
<label>adjust rating(between 1 and 5):</label> <input type="text" name="rate">
<br>
<input type="submit" name = "print" value = "list all users and their rating"> 
<input type="submit" name = "update" value = "update rating"> 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

</form>
</div>




<?php

//this tells the system that it's no longer just parsing 
//html; it's now parsing PHP

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_z4m0b", "a26370149", "dbhost.ugrad.cs.ubc.ca:1522/ug");



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

function printResult($result) { //prints results from a select statement
	
	echo "<table>";
	echo "<br>";
	echo "<tr><th>username</th><th>rating</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {

		echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>"; //or just use "echo $row[0]" 
	}

	echo "<br>";
	echo "</table>";

}

function printResult2($result) { //prints results from a select statement
	
	echo "<table>";
	echo "<br>";
	echo "<tr><th>username</th><th>phone_number</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {

		echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>"; //or just use "echo $row[0]" 
	}

	echo "<br>";
	echo "</table>";

}

// Connect Oracle...0
if ($db_conn) { 

	if (array_key_exists('divide', $_POST)) {
		//echo "you just clicked division";
		//$user = $_POST['user'];
		//what items are being transacted by all users above a certain rating?
		 $price = $_POST['price'];
		 $max = executePlainSQL("select max(price) from Listings_Post");
		 $maxprice = OCI_Fetch_Array($max, OCI_BOTH);
		 
		// find the user that posted all listings with price > x
		 if($price == null){
		 	print("<script>window.alert('invalid input');</script>");
		 }

		 else if($price >= $maxprice[0]){
		 	print("<script>window.alert('lower bound out of range, higher than the most expensive listings $maxprice[0]');</script>");
		 }

		 else{

		 $result = executePlainSQL("SELECT a.username, a.phone FROM account a WHERE a.username = ALL
		 	(SELECT p.username FROM Listings_Post p where p.price > $price)");
		 printResult2($result);}


	/*	 SELECT * FROM account_user sx WHERE NOT EXISTS ((SELECT p.username FROM Listings_Post p where p.price > 50) EXCEPT (SELECT sp.username FROM  account_user sp WHERE sp.username = sx.username ) );
*/
			
		
			/*echo "<br>User accounts<br>";	
			$u1 = executePlainSQL("select * from account a, account_user au where a.username = au.username");			
		    printResult($u1);
			
			echo "<br>Admin accounts<br>";	
			$a1 = executePlainSQL("select * from account a, account_admin ad where a.username = ad.username");
		    printResult($a1);*/
			
		//$result = executePlainSQL("SELECT ItemName from Item i where not exists ((select username from account_user where rating >= $rating) except (select )");
			//printResult($result)


/*			$admin = executePlainSQL("SELECT username from account_admin where username = '$user'");
			$row = OCI_Fetch_Array($result, OCI_BOTH);
			$row_ad = OCI_Fetch_Array($admin, OCI_BOTH);
		*/
		
		
	/*		if($row[0]!=NULL){
				
			if($row_ad[0] != NULL){
				//echo "login through admin account";
				setcookie("admin",true);
				setcookie("username", $user);
				header("location:admin.php");
				}
				
			else{
				//echo "login through normal account";
			setcookie("username", $user);
			header("location:hello.php");
			} 
			}
			else{print("<script>window.alert('Invalid Username or Password, please try again');</script>");
     	
     } */
		OCICommit($db_conn);

	} else
		if (array_key_exists('list', $_POST)) {
			
				$results = executePlainSQL("select username, rating from account_user");
				printResult($results);
					
			
			/*if (!empty($userreg) || !empty($passreg)) {
				
				$check = executePlainSQL("select username from account where username = '$userreg'");
				$rowcheck = OCI_Fetch_Array($check, OCI_BOTH);
				
				if($rowcheck[0]!= NULL){	
				echo "<br>username    " . $userreg . "    already exists<br>";

			
			}
				
			else {//Getting the values from user and insert data into the table
			$tuple = array (
				":bind1" => $_POST['user'],
				":bind2" => $_POST['pass']
			);
			$alltuples = array (
				$tuple
			);


			executeBoundSQL("insert into account values (:bind1, :bind2,null,null,null)", $alltuples);
			executeBoundSQL("insert into account_user values (:bind1, null,null)", $alltuples);
			
			
			
			/*
			$result = executePlainSQL("select * from account");
				printResult($result);
			}
			
			}
			
			else {print("<script>window.alert('Invalid username or password (cannot be empty)');</script>");
				//echo "<br>Invalid username or password (cannot be NULL)<br>";
				}*/
			
			OCICommit($db_conn);
			


		}else
		if (array_key_exists('deleteuser', $_POST)) {
			
			
			$use = $_POST['user'];

			$check = executePlainSQL("select * from account_user where username = '$use'");
			$rowcheck = OCI_Fetch_Array($check, OCI_BOTH);

			if ($rowcheck[0] != NULL){
				executePlainSQL("delete from account_user where username = '$use'");
				executePlainSQL("delete from CreditCard where username = '$use'");
				executePlainSQL("delete from Listings_Post where username = '$use'");
				executePlainSQL("delete from TransactionRecord_Records where username = '$use'");
				executePlainSQL("delete from account where username = '$use'");
				print("<script>window.alert('success');</script>");

			}

			else {print("<script>window.alert('username does not exist');</script>");}		
			
			
			/*if (!empty($userreg) || !empty($passreg)) {
				
				$check = executePlainSQL("select username from account where username = '$userreg'");
				$rowcheck = OCI_Fetch_Array($check, OCI_BOTH);
				
				if($rowcheck[0]!= NULL){	
				echo "<br>username    " . $userreg . "    already exists<br>";

			
			}
				
			else {//Getting the values from user and insert data into the table
			$tuple = array (
				":bind1" => $_POST['user'],
				":bind2" => $_POST['pass']
			);
			$alltuples = array (
				$tuple
			);


			executeBoundSQL("insert into account values (:bind1, :bind2,null,null,null)", $alltuples);
			executeBoundSQL("insert into account_user values (:bind1, null,null)", $alltuples);
			
			
			
			/*
			$result = executePlainSQL("select * from account");
				printResult($result);
			}
			
			}
			
			else {print("<script>window.alert('Invalid username or password (cannot be empty)');</script>");
				//echo "<br>Invalid username or password (cannot be NULL)<br>";
				}*/
			
			OCICommit($db_conn);
			


		} else
			if (array_key_exists('print', $_POST)) {
				/*// Update tuple using data from user
				$tuple = array (
					":bind1" => $_POST['oldName'],
					":bind2" => $_POST['newName']
				);
				$alltuples = array (
					$tuple
				);
				executeBoundSQL("update tab1 set name=:bind2 where name=:bind1", $alltuples);*/
/*
				$use = $_POST['user'];

				$check = executePlainSQL("select * from creditcard where username = '$use'");
				$rowcheck = OCI_Fetch_Array($check, OCI_BOTH);*/

				$results = executePlainSQL("select username, rating from account_user");
				printResult($results);

/*
			if ($rowcheck[0] != NULL){
				//executePlainSQL("delete from account_user where username = '$use'");
				executePlainSQL("delete from CreditCard where username = '$use'");
				//executePlainSQL("delete from Listings_Post where username = '$use'");
				//executePlainSQL("delete from TransactionRecord_Records where username = '$use'");
				//executePlainSQL("delete from account where username = '$use'");
				print("<script>window.alert('success');</script>");

			}

			else {print("<script>window.alert('credircard information for user $use does not exist');</script>");}*/
				OCICommit($db_conn);

			}else
			if (array_key_exists('update', $_POST)) {
				/*// Update tuple using data from user
				$tuple = array (
					":bind1" => $_POST['oldName'],
					":bind2" => $_POST['newName']
				);
				$alltuples = array (
					$tuple
				);
				executeBoundSQL("update tab1 set name=:bind2 where name=:bind1", $alltuples);*/
/*
				$use = $_POST['user'];

				$check = executePlainSQL("select * from creditcard where username = '$use'");
				$rowcheck = OCI_Fetch_Array($check, OCI_BOTH);*/
				$user = $_POST['user'];
				$rate = $_POST['rate']; 

				if($user == '' || $rate == ''){
					print("<script>window.alert('Invalid input, please try again');</script>");
				}

				else{

				executePlainSQL("update account_user set rating = $rate where username = '$user'");

				$results = executePlainSQL("select username, rating from account_user");
				printResult($results);
				}

/*
			if ($rowcheck[0] != NULL){
				//executePlainSQL("delete from account_user where username = '$use'");
				executePlainSQL("delete from CreditCard where username = '$use'");
				//executePlainSQL("delete from Listings_Post where username = '$use'");
				//executePlainSQL("delete from TransactionRecord_Records where username = '$use'");
				//executePlainSQL("delete from account where username = '$use'");
				print("<script>window.alert('success');</script>");

			}

			else {print("<script>window.alert('credircard information for user $use does not exist');</script>");}*/
				OCICommit($db_conn);

			}
			else
			if (array_key_exists('deletecc', $_POST)) {
				/*// Update tuple using data from user
				$tuple = array (
					":bind1" => $_POST['oldName'],
					":bind2" => $_POST['newName']
				);
				$alltuples = array (
					$tuple
				);
				executeBoundSQL("update tab1 set name=:bind2 where name=:bind1", $alltuples);*/

				$use = $_POST['user'];

				$check = executePlainSQL("select * from creditcard where username = '$use'");
				$rowcheck = OCI_Fetch_Array($check, OCI_BOTH);

			if ($rowcheck[0] != NULL){
				//executePlainSQL("delete from account_user where username = '$use'");
				executePlainSQL("delete from CreditCard where username = '$use'");
				//executePlainSQL("delete from Listings_Post where username = '$use'");
				//executePlainSQL("delete from TransactionRecord_Records where username = '$use'");
				//executePlainSQL("delete from account where username = '$use'");
				print("<script>window.alert('success');</script>");

			}

			else {print("<script>window.alert('credircard information for user $use does not exist');</script>");}
				OCICommit($db_conn);

			} 
			else
			if (array_key_exists('deletepost', $_POST)) {
				/*// Update tuple using data from user
				$tuple = array (
					":bind1" => $_POST['oldName'],
					":bind2" => $_POST['newName']
				);
				$alltuples = array (
					$tuple
				);
				executeBoundSQL("update tab1 set name=:bind2 where name=:bind1", $alltuples);*/

				$pos = $_POST['post'];

				$check = executePlainSQL("select * from Listings_Post where listingid = '$pos'");
				$rowcheck = OCI_Fetch_Array($check, OCI_BOTH);

			if ($rowcheck[0] != NULL){
				
				executePlainSQL("delete from TransactionRecord_Records where listingid = '$pos'");
				executePlainSQL("delete from game g where g.itemID = (select i.itemID from item i where listingid = '$pos')");
				executePlainSQL("delete from Accessory a where a.itemID = (select i.itemID from item i where listingid = '$pos')");

				executePlainSQL("delete from item where listingid = '$pos'");
				
				executePlainSQL("delete from Listings_Post where listingid = '$pos'");

				print("<script>window.alert('success');</script>");

			}

			else {print("<script>window.alert('post no.$pos does not exist');</script>");}
				OCICommit($db_conn);

			}
			else
				if (array_key_exists('init', $_POST)) {	
			
			     //print("<script>window.alert('This is a javascript alert from PHP');</script>");
	
		

					
					
			
					/* // Insert data into table...
					executePlainSQL("insert into tab1 values (10, 'Frank')");
					// Inserting data into table using bound variables
					$list1 = array (
						":bind1" => 6,
						":bind2" => "All"
					);
					$list2 = array (
						":bind1" => 7,
						":bind2" => "John"
					);
					$allrows = array (
						$list1,
						$list2
					);
					executeBoundSQL("insert into tab1 values (:bind1, :bind2)", $allrows); //the function takes a list of lists
					// Update data...
					//executePlainSQL("update tab1 set nid=10 where nid=2");
					// Delete data...
					//executePlainSQL("delete from tab1 where nid=1"); */
					OCICommit($db_conn);
				}

	if ($_POST && $success) {
		//POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
		//header("location: oracle-test.php");
	} else {
		// Select data...
		// $result = executePlainSQL("select * from tab1");
		// printResult($result);
	}

	//Commit to save changes...
	OCILogoff($db_conn);
} else {
	echo "cannot connect";
	$e = OCI_Error(); // For OCILogon errors pass no handle
	echo htmlentities($e['message']);
}

/* OCILogon() allows you to log onto the Oracle database
     The three arguments are the username, password, and database
     You will need to replace "username" and "password" for this to
     to work. 
     all strings that start with "$" are variables; they are created
     implicitly by appearing on the left hand side of an assignment 
     statement */

/* OCIParse() Prepares Oracle statement for execution
      The two arguments are the connection and SQL query. */
/* OCIExecute() executes a previously parsed statement
      The two arguments are the statement which is a valid OCI
      statement identifier, and the mode. 
      default mode is OCI_COMMIT_ON_SUCCESS. Statement is
      automatically committed after OCIExecute() call when using this
      mode.
      Here we use OCI_DEFAULT. Statement is not committed
      automatically when using this mode */

/* OCI_Fetch_Array() Returns the next row from the result data as an  
     associative or numeric array, or both.
     The two arguments are a valid OCI statement identifier, and an 
     optinal second parameter which can be any combination of the 
     following constants:

     OCI_BOTH - return an array with both associative and numeric 
     indices (the same as OCI_ASSOC + OCI_NUM). This is the default 
     behavior.  
     OCI_ASSOC - return an associative array (as OCI_Fetch_Assoc() 
     works).  
     OCI_NUM - return a numeric array, (as OCI_Fetch_Row() works).  
     OCI_RETURN_NULLS - create empty elements for the NULL fields.  
     OCI_RETURN_LOBS - return the value of a LOB of the descriptor.  
     Default mode is OCI_BOTH.  */
?>





</body>
</html>
