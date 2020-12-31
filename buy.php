<!DOCTYPE HTML>
<html> 


<head>
<link rel="stylesheet" href="main_style.css">
</head>

<a href="<?php echo 'main.php'?>">
    <?php echo "Home";?>
</a>




<body>

			<?php 
			$itemtype2 = 'Game';
$login = $_COOKIE["username"];
?>

	<label><br><p>Welcome, <?php 
$login = $_COOKIE["username"];
echo $login;
?>
</p></label>

	<label><br><br><p>Create a buying listing</p></label>
	<br><br>


<form method="POST" action="buy.php" >
<label style = "margin:10px">
<p>Listing Title*:</label></p>
<input type="text" name="title"><br>

<label><p>Item Name*:</p></label><input type="text" name="iname"><br><br>

<label>Item Type*:</label>
<input type="radio" name="itemtype" <?php if (isset($itemtype2) && $itemtype2 == 'Game') echo "checked";?> value = "Game">
	<label>Game</label>
<input type="radio" name="itemtype" <?php if (isset($itemtype2) && $itemtype2 == 'Accessory') echo "checked";?> value = "Accessory">
	<label>Accessory</label><br><br>

<label><p>Price*:</p></label> <br><input type="number" name="price"><br>

<label><p>Trade Location*:</p></label> <br><input type="text" name="location"><br>
<br><label>*:Required field</label><br>
<input type="submit" name = "create" value = "Create!"> 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<br><br><br><br><br>



</form>
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
	echo "<tr><th>username</th><th>password</th><th>recovery</th><th>member_since</th><th>phone</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {

		echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td><td>". $row[2] . "</td><td>". $row[3] . "</td><td>". $row[4] . "</td></tr>"; //or just use "echo $row[0]" 
	}

	echo "<br>";
	echo "</table>";

}

// Connect Oracle...0
if ($db_conn) {



	 

	if (array_key_exists('create', $_POST)) {
		//echo "you just clicked login";
		
		$title = $_POST['title'];
		$price = $_POST['price'];
		$location = $_POST['location'];
		$iname = $_POST['iname'];
		$itemtype = $_POST['itemtype'];

		if($title == '' || $price == '' || $location == '' || $iname == '' || $itemtype == ''){
			print("<script>window.alert('missing fields');</script>");
		}
		else{
		
		$result = executePlainSQL("select count(*) from Listings_Post");
		$row = OCI_Fetch_Array($result, OCI_BOTH);
		$today =  date("Y-m-d"); 

		$resultitem = executePlainSQL("select count(*) from item");
		$rowitem = OCI_Fetch_Array($resultitem, OCI_BOTH);
		

		$newlistingid = 20+$row[0];
		$newitemid = 10+$rowitem[0];

		executePlainSQL("insert into Listings_Post values ('$login', '$newlistingid','true','false','$title',$price,'$location','$today')");

		executePlainSQL("insert into Item values ('$newitemid', '$newlistingid','$iname', null, null, null)");

		if($itemtype == 'Game'){
			executePlainSQL("insert into Game values ('$newitemid', null, null, null, null)");
		}
		else {
			executePlainSQL("insert into Accessory values ('$newitemid', null,null)");
		}


		print("<script>window.alert('success');</script>");}

		

/*			executePlainSQL("insert into account values ('a', '1',null,null,null)");
			executePlainSQL("insert into account_admin values ('a', null)");
		
			echo "<br>User accounts<br>";	
			$u1 = executePlainSQL("select * from account a, account_user au where a.username = au.username");			
		    printResult($u1);
			
			echo "<br>Admin accounts<br>";	
			$a1 = executePlainSQL("select * from account a, account_admin ad where a.username = ad.username");
		    printResult($a1);
			
			$result = executePlainSQL("SELECT username from account where username = '$user' AND password = '$pass'");
			$admin = executePlainSQL("SELECT username from account_admin where username = '$user'");
			$row = OCI_Fetch_Array($result, OCI_BOTH);
			$row_ad = OCI_Fetch_Array($admin, OCI_BOTH);
		
		
		
			if($row[0]!=NULL){
				
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
		if (array_key_exists('register', $_POST)) {
			
			echo "you just clicked register";
			$userreg = $_POST['user'];
			$passreg = $_POST['pass'];
			
			
			if (!empty($userreg) || !empty($passreg)) {
				
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
				printResult($result);*/
			}
			
			}
			
			else {print("<script>window.alert('Invalid username or password (cannot be empty)');</script>");
				//echo "<br>Invalid username or password (cannot be NULL)<br>";
				}
			
			OCICommit($db_conn);
			


		} else
			if (array_key_exists('updatesubmit', $_POST)) {
				// Update tuple using data from user
				$tuple = array (
					":bind1" => $_POST['oldName'],
					":bind2" => $_POST['newName']
				);
				$alltuples = array (
					$tuple
				);
				executeBoundSQL("update tab1 set name=:bind2 where name=:bind1", $alltuples);
				OCICommit($db_conn);

			} else
				if (array_key_exists('init', $_POST)) {	
			
			     //print("<script>window.alert('This is a javascript alert from PHP');</script>");
	
	
					executePlainSQL("INSERT
INTO          Account(username, Password, Recovery, member_since, phone)
VALUES     ('John_Doe', 'LuggageCombo', 'johnd@gmail.com', TO_DATE('2018-01-01', 'yyyy-mm-dd'), '604-111-1111')");
					executePlainSQL("INSERT
INTO          Account(username, Password, Recovery, member_since, phone)
VALUES     ('Pac_man', 'cherry', 'pacpac@hotmail.com', TO_DATE('2017-02-02', 'yyyy-mm-dd'), '604-222-2222')");
					executePlainSQL("INSERT
INTO          Account(username, Password, Recovery, member_since, phone)
VALUES     ('Agent47', 'silverballer', 'agent47@yahoo.ca', TO_DATE('2017-03-03', 'yyyy-mm-dd'), '604-474-4747')");
					executePlainSQL("INSERT
INTO          Account(username, Password, Recovery, member_since, phone)
VALUES     ('Solid_Snake', 'cardboard-box', 'kojima@gmail.com', TO_DATE('2017-04-04', 'yyyy-mm-dd'), '604-444-4444')");
					executePlainSQL("INSERT
INTO          Account(username, Password, Recovery, member_since, phone)
VALUES     ('Lara_Croft', 'treasure', 'croft@hotmail.com', TO_DATE('2017-05-05', 'yyyy-mm-dd'), '604-555-5555')");
					executePlainSQL("INSERT
INTO          Account(username, Password, Recovery, member_since, phone)
VALUES     ('Mario', 'luigi', 'world1@yahoo.ca', TO_DATE('2017-06-06', 'yyyy-mm-dd'), '604-666-6666')");
					executePlainSQL("INSERT
INTO          Account(username, Password, Recovery, member_since, phone)
VALUES     ('Leon_Kennedy', '9000zombies', 're4@gmail.com', TO_DATE('2017-07-07', 'yyyy-mm-dd'), '604-777-7777')");
					executePlainSQL("INSERT
INTO          Account(username, Password, Recovery, member_since, phone)
VALUES     ('Dante', 'DevilMaySmile', 'lucifer@gmail.com', TO_DATE('2017-08-08', 'yyyy-mm-dd'), '604-888-8888')");
					executePlainSQL("INSERT
INTO          Account(username, Password, Recovery, member_since, phone)
VALUES     ('Lightning', 'chocobo', 'ffxiii@hotmail.com', TO_DATE('2017-09-09', 'yyyy-mm-dd'), '604-999-9999')");
					executePlainSQL("INSERT
INTO          Account(username, Password, Recovery, member_since, phone)
VALUES     ('Captain_Price', 'soap', 'mw9000@gmail.com', TO_DATE('2017-10-10', 'yyyy-mm-dd'), '604-123-1234')");
					executePlainSQL("INSERT
INTO          Account_Admin(username, adminID)
VALUES     ('John_Doe', 'a1')");
					executePlainSQL("INSERT
INTO          Account_Admin(username, adminID)
VALUES     ('Pac_man', 'a2')");
					executePlainSQL("INSERT
INTO          Account_Admin(username, adminID)
VALUES     ('Agent47', 'a3')");
					executePlainSQL("INSERT
INTO          Account_Admin(username, adminID)
VALUES     ('Solid_Snake', 'a4')");
					executePlainSQL("INSERT
INTO          Account_Admin(username, adminID)
VALUES     ('Lara_Croft', 'a5')");
					executePlainSQL("INSERT
INTO          Account_User(username, wallet_amount, Rating)
VALUES     ('Mario', 50, 1)");
					executePlainSQL("INSERT
INTO          Account_User(username, wallet_amount, Rating)
VALUES     ('Leon_Kennedy', 60, 2)");
					executePlainSQL("INSERT
INTO          Account_User(username, wallet_amount, Rating)
VALUES     ('Dante', 70, 3)");
					executePlainSQL("INSERT
INTO          Account_User(username, wallet_amount, Rating)
VALUES     ('Lightning', 80, 4)");
					executePlainSQL("INSERT
INTO          Account_User(username, wallet_amount, Rating)
VALUES     ('Captain_Price', 90, 5)");
					executePlainSQL("INSERT
INTO          CreditCard(Username, card, ExpDate, CVC)
VALUES     ('Mario', 1113451305040000, '01/20', 123)");
					executePlainSQL("INSERT
INTO          CreditCard(Username, card, ExpDate, CVC)
VALUES     ('Leon_Kennedy', 2468112398765555, '02/21', 456)");
					executePlainSQL("INSERT
INTO          CreditCard(Username, card, ExpDate, CVC)
VALUES     ('Dante', 3321554322142543, '05/20', 789)");
					executePlainSQL("INSERT
INTO          CreditCard(Username, card, ExpDate, CVC)
VALUES     ('Lightning', 1111111111111111, '09/20', 135)");
					executePlainSQL("INSERT
INTO          CreditCard(Username, card, ExpDate, CVC)
VALUES     ('Captain_Price', 0000231221116152, '08/21', 246)");
					executePlainSQL("INSERT
INTO          Listings_Post(username, listingID, Active, Selling, Title, Price, Location, DatePosted)
VALUES     ('Mario', '5', 'false', 'true', 'Mario Party 6', 80, 'Vancouver', TO_DATE('2017-10-28', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Listings_Post(username, listingID, Active, Selling, Title, Price, Location, DatePosted)
VALUES     ('Leon_Kennedy', '6', 'true', 'false', 'Resident Evil 4', 30, 'Vancouver', TO_DATE('2017-11-08', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Listings_Post(username, listingID, Active, Selling, Title, Price, Location, DatePosted)
VALUES     ('Dante', '7', 'false', 'true', 'Devil May Cry 3', 20, 'Richmond', TO_DATE('2017-11-24', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Listings_Post(username, listingID, Active, Selling, Title, Price, Location, DatePosted)
VALUES     ('Lightning', '8', 'true', 'true', 'PS3 Console', 20, 'North Vancouver', TO_DATE('2017-12-30', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Listings_Post(username, listingID, Active, Selling, Title, Price, Location, DatePosted)
VALUES     ('Captain_Price', '9', 'true', 'false', 'Nintendo 3DS', 30, 'Coquitlam', TO_DATE('2017-12-25', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Listings_Post(username, listingID, Active, Selling, Title, Price, Location, DatePosted)
VALUES     ('Mario', '10', 'true', 'true', 'Persona 5', 100, 'Vancouver', TO_DATE('2018-02-11', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Listings_Post(username, listingID, Active, Selling, Title, Price, Location, DatePosted)
VALUES     ('Mario', '11', 'false', 'false', 'Sonic the Hedgehog', 99, 'Vancouver', TO_DATE('2017-05-08', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Listings_Post(username, listingID, Active, Selling, Title, Price, Location, DatePosted)
VALUES     ('Dante', '12', 'true', 'true', 'Xbox 360 Controller', 120, 'Richmond', TO_DATE('2017-12-24', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Listings_Post(username, listingID, Active, Selling, Title, Price, Location, DatePosted)
VALUES     ('Lightning', '13', 'true', 'true', 'Xbox Kinect', 20, 'North Vancouver', TO_DATE('2017-12-30', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Listings_Post(username, listingID, Active, Selling, Title, Price, Location, DatePosted)
VALUES     ('Captain_Price', '14', 'true', 'false', 'Switch Joycon', 30, 'Coquitlam', TO_DATE('2017-11-25', 'yyyy-mm-dd'))");


					executePlainSQL("INSERT
INTO          TransactionRecord_Records(username, TransactionID, DateSold,listingID)
VALUES     ('Mario', 't1', TO_DATE('2018-01-05', 'yyyy-mm-dd') , '10')");
					executePlainSQL("INSERT
INTO          TransactionRecord_Records(username, TransactionID, DateSold,listingID)
VALUES     ('Leon_Kennedy', 't2', TO_DATE('2018-01-10', 'yyyy-mm-dd'), '6')");
					executePlainSQL("INSERT
INTO          TransactionRecord_Records(username, TransactionID, DateSold,listingID)
VALUES     ('Dante', 't3', TO_DATE('2018-01-20', 'yyyy-mm-dd'), '12')");
					executePlainSQL("INSERT
INTO          TransactionRecord_Records(username, TransactionID, DateSold,listingID)
VALUES     ('Lightning', 't4', TO_DATE('2018-01-24', 'yyyy-mm-dd'), '8')");
					executePlainSQL("INSERT
INTO          TransactionRecord_Records(username, TransactionID, DateSold,listingID)
VALUES     ('Captain_Price', 't5', TO_DATE('2018-02-15', 'yyyy-mm-dd'), '9')");
					




					executePlainSQL("INSERT
INTO          Company(CompanyName, location, founded)
VALUES     ('Nintendo', 'Kyoto, Japan', TO_DATE('1889-09-23', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Company(CompanyName, location, founded)
VALUES     ('Sega', 'Shinagawa, Tokyo, Japan', TO_DATE('1960-05-03', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Company(CompanyName, location, founded)
VALUES     ('Atlus', 'Setagaya, Tokyo, Japan', TO_DATE('1986-04-07', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Company(CompanyName, location, founded)
VALUES     ('Electronic Arts', 'Redwood City, California, U.S.', TO_DATE('1982-05-27', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Company(CompanyName, location, founded)
VALUES     ('Arc System Works', 'Yokohama, Japan', TO_DATE('1988-05-01', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Company(CompanyName, location, founded)
VALUES     ('Microsoft', 'Washington, U.S.', TO_DATE('1975-04-04', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Company(CompanyName, location, founded)
VALUES     ('Konami', 'Kyoto, Japan', TO_DATE('1969-03-21', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Company(CompanyName, location, founded)
VALUES     ('Sony', 'Tokyo, Japan', TO_DATE('1949-03-21', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Company(CompanyName, location, founded)
VALUES     ('Monolift Soft', 'Kyoto, Japan', TO_DATE('1999-10-01', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Company(CompanyName, location, founded)
VALUES     ('Capcom Vancouver', 'Vancouver, Canada', TO_DATE('2005-07-04', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Company(CompanyName, location, founded)
VALUES     ('Capcom', 'Osaka, Japan', TO_DATE('1979-05-30', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Company(CompanyName, location, founded)
VALUES     ('Bioware', 'Edmonton, Canada', TO_DATE('1995-02-01', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Company(CompanyName, location, founded)
VALUES     ('Retro Studios', 'Austin, Texas, U.S.', TO_DATE('1998-10-01', 'yyyy-mm-dd'))");
					executePlainSQL("INSERT
INTO          Owned_by(parent_company, child_company)
VALUES     ('Nintendo', 'Monolift Soft')");
					executePlainSQL("INSERT
INTO          Owned_by(parent_company, child_company)
VALUES     ('Sega', 'Atlus')");
					executePlainSQL("INSERT
INTO          Owned_by(parent_company, child_company)
VALUES     ('Capcom', 'Capcom Vancouver')");
					executePlainSQL("INSERT
INTO          Owned_by(parent_company, child_company)
VALUES     ('Electronic Arts', 'Bioware')");
					executePlainSQL("INSERT
INTO          Owned_by(parent_company, child_company)
VALUES     ('Nintendo', 'Retro Studios')");



					executePlainSQL("INSERT
INTO          Item(itemID, listingID, ItemName,  CompanyName, platform, MSRP)
VALUES     ('s1', '5',  'Mario Party 6', 'Nintendo', 'Gamecube', 60)");
					executePlainSQL("INSERT
INTO          Item(itemID, listingID, ItemName, CompanyName, platform, MSRP)
VALUES     ('s2', '6', 'Resident Evil 4', 'Konami', 'PC', 25)");
					executePlainSQL("INSERT
INTO          Item(itemID, listingID, ItemName, CompanyName, platform, MSRP)
VALUES     ('s3', '7', 'Devil May Cry 3', 'Capcom', 'PS3', 60)");
					executePlainSQL("INSERT
INTO          Item(itemID, listingID, ItemName, CompanyName, platform, MSRP)
VALUES     ('s4', '10', 'Persona 5', 'Atlus', 'PS4', 80)");
					executePlainSQL("INSERT
INTO          Item(itemID, listingID, ItemName, CompanyName, platform, MSRP)
VALUES     ('s5', '11', 'Sonic the Hedgehog', 'Sega', 'Game Gear', 40)");


					executePlainSQL("INSERT
INTO          Item(itemID, listingID, ItemName, CompanyName, platform, MSRP)
VALUES     ('p1', '8', 'PS3 Controller', 'Sony', 'PS3', 60)");
					executePlainSQL("INSERT
INTO          Item(itemID, listingID, ItemName, CompanyName, platform, MSRP)
VALUES     ('p2', '9', 'Nintendo 3DS', 'Nintendo', '3DS', 200)");
					executePlainSQL("INSERT
INTO          Item(itemID, listingID, ItemName, CompanyName, platform, MSRP)
VALUES     ('p3', '12', 'Xbox 360 Controller', 'Microsoft', 'Xbox 360', 50)");
					executePlainSQL("INSERT
INTO          Item(itemID, listingID, ItemName, CompanyName, platform, MSRP)
VALUES     ('p4', '13', 'Xbox Kinect', 'Microsoft', 'Xbox 360', 100)");
					executePlainSQL("INSERT
INTO          Item(itemID, listingID, ItemName, CompanyName, platform, MSRP)
VALUES     ('p5', '14', 'Switch Joycon', 'Nintendo', 'Switch', 90)");



					executePlainSQL("INSERT
INTO          Game(itemID, release_date, genre, franchise, players)
VALUES     ('s1', '2004', 'party', 'Mario', 4)");
					executePlainSQL("INSERT
INTO          Game(itemID, release_date, genre, franchise, players)
VALUES     ('s2', '2005', 'action', 'Resident Evil', 2)");
					executePlainSQL("INSERT
INTO          Game(itemID, release_date, genre, franchise, players)
VALUES     ('s3', '2005', 'hack and slash', 'Devil May Cry', 1)");
					executePlainSQL("INSERT
INTO          Game(itemID, release_date, genre, franchise, players)
VALUES     ('s4', '2017', 'jrpg', 'Persona', 1)");
					executePlainSQL("INSERT
INTO          Game(itemID, release_date, genre, franchise, players)
VALUES     ('s5', '1991', 'platformer', 'Sonic the Hedgehog', 1)");
					executePlainSQL("INSERT
INTO          Accessory(itemID, Colour, Type)
VALUES     ('p1', 'Black', 'Controller')");
					executePlainSQL("INSERT
INTO          Accessory(itemID, Colour, Type)
VALUES     ('p2', 'Blue', 'Console')");
					executePlainSQL("INSERT
INTO          Accessory(itemID, Colour, Type)
VALUES     ('p3', 'White', 'Controller')");
					executePlainSQL("INSERT
INTO          Accessory(itemID, Colour, Type)
VALUES     ('p4', 'Black', 'Other')");
					executePlainSQL("INSERT
INTO          Accessory(itemID, Colour, Type)
VALUES     ('p5', 'Red and Blue', 'Controller')");				

					
					
			
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
