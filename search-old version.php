<html>
<head>
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<style>
.error {color: #FF0000;}
</style>
<link rel="stylesheet" href="main_style.css">
</head>

<a href="<?php echo 'main.php'?>">
    <?php echo "Home";?>
</a>


<p> 
<?php 
	
	$listingType2 = 'both';
	$cheapest2 = 'none';
	$priceLimit2 = 'max'; 
	$MSRPLimit2 = 'max';
	$sort2 = 'lp.dateposted';
	//getcookie("user");//$_COOKIE["username"];
	//echo $username;
	//echo "!";
	//	a href = 'hello.php?id="
	//<a href="/company_details.php?id=<?php echo $companyId; 

?>
<label>Search For Listings</label>


<br>
<!--<input type="submit" name = "account" value = "Profile">-->
<body>
	<form method="POST" action="search.php" >
	Search by item name: <br>
	<input type="text" name="searchTitle"><br>
	<!--TODO: Add ability to change this to min price-->
	Search by company: <br>
	<input type="text" name="searchCompany"><br>
	Search by
	<input type="radio" name="priceLimit" <?php if (isset($priceLimit2) && $priceLimit2 == 'min') echo "checked";?> value = "min">
	minimum
	<input type="radio" name="priceLimit" <?php if (isset($priceLimit2) && $priceLimit2 == 'max') echo "checked";?> value = "max">
	maximum
	price: <br>
	<input type="text" name="searchPrice"><br>
	Search by platform: <br>
	<input type="text" name="searchPlatform"><br>
	Search by
	<input type="radio" name="MSRPLimit" <?php if (isset($MSRPLimit2) && $MSRPLimit2 == 'min') echo "checked";?> value = "min">
	minimum
	<input type="radio" name="MSRPLimit" <?php if (isset($MSRPLimit2) && $MSRPLimit2 == 'max') echo "checked";?> value = "max">
	maximum
	MSRP: <br>
	<input type="text" name="searchMSRP"><br>
	Search by location: <br>
	<input type="text" name="searchLocation"><br>
	<!-- Search by date posted: <br>
	<input type="text" name="searchDate"><br> -->
	Search by poster: <br>
	<input type="text" name="searchUser"><br>
	<!--Only show cheapest listing <input type="checkbox" name="cheapest"><br> -->
	Limit Results By Most/Least Expensive <br>
	<input type="radio" name="cheapest" <?php if (isset($cheapest2) && $cheapest2 == 'min') echo "checked";?> value = "min">
	Cheapest
	<input type="radio" name="cheapest" <?php if (isset($cheapest2) && $cheapest2 == 'max') echo "checked";?> value = "max">
	Most Expensive
	<input type="radio" name="cheapest" <?php if (isset($cheapest2) && $cheapest2 == 'none') echo "checked";?> value = "none">
	None
	<br>
	Limit Results By Listing Type <br>
	<input type="radio" name="listingType" <?php if (isset($listingType2) && $listingType2 == 'buy') echo "checked";?> value = "buy">
	Buy
	<input type="radio" name="listingType" <?php if (isset($listingType2) && $listingType2 == 'sell') echo "checked";?> value = "sell">
	Sell
	<input type="radio" name="listingType" <?php if (isset($listingType2) && $listingType2 == 'both') echo "checked";?> value = "both">
	Both
	<br>
	Base results on company average <input type="checkbox" name="companyAvg"><br>
	Include Inactive Listings <input type="checkbox" name="inactive"><br>
	Show Minimal Listings <input type="checkbox" name="minimal"><br>
	Sort by:
	<input type="radio" name="sort" <?php if (isset($sort2) && $sort2 == 'lp.title') echo "checked";?> value = "lp.title">
	title
	<input type="radio" name="sort" <?php if (isset($sort2) && $sort2 == 'i.companyname') echo "checked";?> value = "i.companyname">
	company
	<input type="radio" name="sort" <?php if (isset($sort2) && $sort2 == 'lp.price') echo "checked";?> value = "lp.price">
	price
	<input type="radio" name="sort" <?php if (isset($sort2) && $sort2 == 'i.platform') echo "checked";?> value = "i.platform">
	platform
	<input type="radio" name="sort" <?php if (isset($sort2) && $sort2 == 'i.msrp') echo "checked";?> value = "i.msrp">
	msrp
	<input type="radio" name="sort" <?php if (isset($sort2) && $sort2 == 'lp.location') echo "checked";?> value = "lp.location">
	location
	<input type="radio" name="sort" <?php if (isset($sort2) && $sort2 == 'lp.username') echo "checked";?> value = "lp.username">
	username
	<input type="radio" name="sort" <?php if (isset($sort2) && $sort2 == 'lp.dateposted') echo "checked";?> value = "lp.dateposted">
	dateposted
	<br>
	<input type="submit" name = "search" value = "Search">
	<!--Query for listings-->

	<p>Your listings:</p>
	<!--Query for listings made by this user-->
	<!--<input type="submit" name = "show" value = "Show Listings"> -->
	<!--<button type="button"
	onclick="document.getElementById('demo').innerHTML = 'HelloWorld'">
	Test.</button>
	<input type="submit" name="init" value="RESET for testing" >
	-->

	<p id="demo"></p>

</form>
<?php

$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_z4m0b", "a26370149", "dbhost.ugrad.cs.ubc.ca:1522/ug");

$username = "";
if (!isset($_COOKIE["username"]))
{
   	$username = $_COOKIE["username"];
}

function console_log( $data ){
  echo '<script>';
  echo 'console.log('. json_encode( $data ) .')';
  echo '</script>';
}

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
	//echo "<br>(for testing only)Got data from Account<br>";
	echo "<table>";
	echo "<tr><th>title</th><th>company</th><th>price</th>
	<th>Platform</th><th>MSRP</th><th>location</th>
	<th>username</th><th>dateposted</th><th>type</th><th>view</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		//Set up type
		$typeString = "";
		
		if ($row[6] == 'true')
		{
			$typeString = 'sell';
		}
		else
		{
			$typeString = 'buy';
		}
		//Display results.
		echo "<tr><td>" . $row[0] . "</td><td>" . $row[7] . "</td><td>" . $row[1]
		 . "</td><td>" . $row[8] . "</td><td>" . $row[9]
		 . "</td><td>" . $row[2] . "</td><td>" . $row[3]
		  . "</td><td>" . $row[4] . "</td><td>" . $typeString
		    . "</td><td> <a href = 'hello.php?id="  . $row[5]
		 . "'> Link</a> </td></tr> <br>"; //or just use "echo $row[0]"
		//echo '<a href = "hello.php"> Link</a>';

	}
	echo "</table>";

}

function printResult2($result) { //prints results from a select statement
	//echo "<br>(for testing only)Got data from Account<br>";
	echo "<table>";
	echo "<tr><th>username</th><th>title</th>
	<th>price</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {

		//Display results.
		echo "<tr><td>" . $row[2] . "</td><td>" . $row[0] . "</td><td>" . $row[1] . " </td></tr> <br>"; //or just use "echo $row[0]"
		//echo '<a href = "hello.php"> Link</a>';

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
		$link = "location:hello.php?username=" + $username; 
		header("location:hello.php?username=" + $username);
	}
	else if (array_key_exists('search', $_POST)) 
	{
		echo "<br>Here are the listings matching your search:<br>";	

			$title = $_POST['searchTitle'];
			$price = $_POST['searchPrice'];
			$location = $_POST['searchLocation'];
			$user = $_POST['searchUser'];
			$cheapest = $_POST['cheapest'];
			$listingType = $_POST['listingType'];
			$companyAvg = $_POST['companyAvg'];
			$inactive = $_POST['inactive'];
			$priceLimit = $_POST['priceLimit'];
			$companyname = $_POST['searchCompany'];
			$platform = $_POST['searchPlatform'];
			$MSRP = $_POST['searchMSRP'];
			$MSRPLimit = $_POST['MSRPLimit'];
			$sort = $_POST['sort'];
			$sortString = '';
			$minimal = $_POST['minimal'];
			if ($cheapest != 'none')
			{
				$limitString = $cheapest;
			}

			
			
			

			$sql = "SELECT DISTINCT lp.title, lp.price, 
			lp.location, lp.username, lp.dateposted, lp.listingid, lp.selling, i.companyname, i.platform, i.msrp 
			FROM Listings_Post lp, Item i 
			WHERE lp.listingid = i.listingid ";
			$sqlMin = "SELECT ". $limitString ."(lp2.price) FROM Listings_Post lp2, Item i WHERE lp2.listingid = i.listingid ";
			$sqlCount = "SELECT COUNT(*) from Listings_Post lp, Item i where lp.listingid = i.listingid ";
			$sqlAvg = "CREATE VIEW AvgCompanyPrice AS SELECT AVG(lp.price) AS avgPrice, i.companyname FROM Listings_Post lp, Item i 
			WHERE lp.listingid = i.listingid ";
			$sqlWhere = ""; //Currently only used for company averaging!
			$sqlm = "SELECT DISTINCT lp.title, lp.price, 
			 lp.username 
			FROM Listings_Post lp, Item i 
			WHERE lp.listingid = i.listingid ";
			if ($inactive == false)
			{
				$sql .= "AND lp.Active = 'true'";
				$sqlMin .= "AND lp2.Active = 'true'";
				$sqlCount .= "AND lp.Active = 'true'";
				$sqlAvg .= "AND lp.Active = 'true'";
				$sqlWhere .= "AND lp.Active = 'true'";
				$sqlm .= "AND lp.Active = 'true'";
			}
			//$sqlMin = "SELECT MIN(lp2.price) FROM Listings_Post lp2 where lp2.Active = 'true'";
			//Handle cheapest checkbox
			// if ($cheapest == true)
			// {
			// 	$sql .= " MIN(price),";
			// 	console_log($cheapest);
			// }
			// else
			// {
			//	$sql .= " ";
			//}
			//$sql .=  "";
			//Append search terms.
			if ($title != "")
			{
				$sql .= "AND lp.title LIKE '%" . $title . "%'";
				$sqlm .= "AND lp.title LIKE '%" . $title . "%'";
				$sqlMin .= "AND lp2.title LIKE '%" . $title . "%'";
				$sqlCount .= "AND lp.title LIKE '%" . $title . "%'";
				$sqlAvg .= "AND lp.title LIKE '%" . $title . "%'";
				$sqlWhere .= "AND lp.title LIKE '%" . $title . "%'";
			}
			if ($companyname != "")
			{
				$sql .= "AND i.companyname LIKE '%" . $companyname . "%'";
				$sqlm .= "AND i.companyname LIKE '%" . $companyname . "%'";
				$sqlMin .= "AND i.companyname LIKE '%" . $companyname . "%'";
				$sqlCount .= "AND i.companyname LIKE '%" . $companyname . "%'";
				$sqlAvg .= "AND i.companyname LIKE '%" . $companyname . "%'";
				$sqlWhere .= "AND i.companyname LIKE '%" . $companyname . "%'";
			}
			if ($platform != "")
			{
				$sql .= "AND i.platform LIKE '%" . $platform . "%'";
				$sqlm .= "AND i.platform LIKE '%" . $platform . "%'";
				$sqlMin .= "AND i.platform LIKE '%" . $platform . "%'";
				$sqlCount .= "AND i.platform LIKE '%" . $platform . "%'";
				$sqlAvg .= "AND i.platform LIKE '%" . $platform . "%'";
				$sqlWhere .= "AND i.platform LIKE '%" . $platform . "%'";
			}
			if ($price != "")
			{
				if (is_numeric ($price))
				{
					if ($priceLimit == 'max')
					{
						$sql .= "AND lp.price <= " . $price;
						$sqlm .= "AND lp.price <= " . $price;
						$sqlMin .= "AND lp2.price <= " . $price;
						$sqlCount .= "AND lp.price <= " . $price;
						$sqlAvg .= "AND lp.price <= " . $price;
						$sqlWhere .= "AND lp.price <= " . $price;
					}
					else
					{
						$sql .= "AND lp.price >= " . $price;
						$sqlm .= "AND lp.price >= " . $price;
						$sqlMin .= "AND lp2.price >= " . $price;
						$sqlCount .= "AND lp.price >= " . $price;
						$sqlAvg .= "AND lp.price >= " . $price;
						$sqlWhere .= "AND lp.price >= " . $price;
					}
					
				}
				else 
				{
					//Give error here
					echo "ALERT: Price must be a numerical value for it to be considered. <br>";
				}
			}
			if ($MSRP != "")
			{
				if (is_numeric ($MSRP))
				{
					if ($MSRPLimit == 'max')
					{
						$sql .= "AND i.msrp <= " . $MSRP;
						$sqlm .= "AND i.msrp <= " . $MSRP;
						$sqlMin .= "AND i.msrp <= " . $MSRP;
						$sqlCount .= "AND i.msrp <= " . $MSRP;
						$sqlAvg .= "AND i.msrp <= " . $MSRP;
						$sqlWhere .= "AND i.msrp <= " . $MSRP;
					}
					else
					{
						$sql .= "AND i.msrp >= " . $MSRP;
						$sqlm .= "AND i.msrp >= " . $MSRP;
						$sqlMin .= "AND i.msrp >= " . $MSRP;
						$sqlCount .= "AND i.msrp >= " . $MSRP;
						$sqlAvg .= "AND i.msrp >= " . $MSRP;
						$sqlWhere .= "AND i.msrp >= " . $MSRP;
					}
					
				}
				else 
				{
					//Give error here
					echo "ALERT: Price must be a numerical value for it to be considered. <br>";
				}
			}
			if ($location != "")
			{
				$sql .= "AND lp.location LIKE '%" . $location . "%'";
				$sqlm .= "AND lp.location LIKE '%" . $location . "%'";
				$sqlMin .= "AND lp2.location LIKE '%" . $location . "%'";
				$sqlCount .= "AND lp.location LIKE '%" . $location . "%'";
				$sqlAvg .= "AND lp.location LIKE '%" . $location . "%'";
				$sqlWhere .= "AND lp.location LIKE '%" . $location . "%'";
			}
			if ($user != "")
			{
				$sql .= "AND lp.username = '" . $user . "'";
				$sqlm .= "AND lp.username = '" . $user . "'";
				$sqlMin .= "AND lp2.username = '" . $user . "'";
				$sqlCount .= "AND lp.username = '" . $user . "'";
				$sqlAvg .= "AND lp.username = '" . $user . "'";
				$sqlWhere .= "AND lp.username = '" . $user . "'";
			}
			if ($listingType == "buy")
			{
				$sql .= "AND lp.selling = 'false'";
				$sqlm .= "AND lp.selling = 'false'";
				$sqlMin .= "AND lp2.selling = 'false'";
				$sqlCount .= "AND lp.selling = 'false'";
				$sqlAvg .= "AND lp.selling = 'false'";
				$sqlWhere .= "AND lp.selling = 'false'";
			} elseif ($listingType == "sell")
			{
				$sql .= "AND lp.selling = 'true'";
				$sqlm .= "AND lp.selling = 'true'";
				$sqlMin .= "AND lp2.selling = 'true'";
				$sqlCount .= "AND lp.selling = 'true'";
				$sqlAvg .= "AND lp.selling = 'true'";
				$sqlWhere .= "AND lp.selling = 'true'";
			} 
			// if ($cheapest == true)
			// {
			// 	$sql .= " AND lp2.active = 'true' AND lp.price < lp2.price";
			// 	$sqlCount .= " AND lp2.active = 'true' AND lp.price < lp2.price";
			// 	console_log($cheapest);
			// }
			if ($cheapest != 'none' && $companyAvg == false)
			{
				$sql .= " AND lp.price = 
				(". $sqlMin . ")";
				$sqlm .= " AND lp.price = 
				(". $sqlMin . ")";
				$sqlCount .= " AND lp.price = 
				(". $sqlMin . ")";
			}

			$sortString = " ORDER BY ".$sort." DESC";
			$sql .= $sortString;
			if ($companyAvg == true && $cheapest != 'none')
			{
				executePlainSQL("DROP VIEW AvgCompanyPrice");
				$sqlAvg .= " GROUP BY i.companyname";
				$sql = "SELECT DISTINCT lp.title, lp.price, 
							lp.location, lp.username, lp.dateposted, lp.listingid, lp.selling, i.companyname, i.platform, i.msrp  
							FROM Listings_Post lp, item i
							WHERE lp.listingid = i.listingid 
							AND i.companyname = 
							(SELECT MIN(companyname) 
								FROM AvgCompanyPrice
								WHERE avgPrice = 
								(SELECT ". $cheapest ."(avgPrice)
									FROM AvgCompanyPrice)
							)" . $sqlWhere . $sortString;
								$sql = "SELECT DISTINCT lp.title, lp.price, 
							lp.username
							FROM Listings_Post lp, item i
							WHERE lp.listingid = i.listingid 
							AND i.companyname = 
							(SELECT MIN(companyname) 
								FROM AvgCompanyPrice
								WHERE avgPrice = 
								(SELECT ". $cheapest ."(avgPrice)
									FROM AvgCompanyPrice)
							)" . $sqlWhere . $sortString;			
				$sqlCount = "SELECT COUNT(*) 
							FROM Listings_Post lp, item i
							WHERE lp.listingid = i.listingid 
							AND i.companyname = 
							(SELECT Min(companyname) 
								FROM AvgCompanyPrice
								WHERE avgPrice = 
								(SELECT ". $cheapest ."(avgPrice)
									FROM AvgCompanyPrice)
							)" . $sqlWhere;
				executePlainSQL($sqlAvg);
			} else if ($companyAvg == true && $cheapest == 'none')
			{
				//Give error here
					echo "ALERT: You must specify to show the least/most expensive result in order for company averaging to be considered. <br>";
			}

			$result = executePlainSQL($sqlCount);
			$row = OCI_Fetch_Array($result, OCI_BOTH);
			if($row[0] > 0)
			{
				echo "Results found: " . $row[0];
				// if ($cheapest == true)
				// {
				// 	$sql = " SELECT sub.* FROM (" . $sql . ") sub WHERE price = MIN(price)";
				// }
				//("SELECT * from Listings_Post lp where lp.Active = 'true' ORDER BY DATEPOSTED DESC");
			//$row = OCI_Fetch_Array($result, OCI_BOTH);
			//if($row[0]!=NULL) //TODO: Fix the condition for this!
			//{
				if ($minimal == true)
				{
					$result = executePlainSQL($sqlm);
					printResult2($result);
				}
		    	else
		    	{
		    		$result = executePlainSQL($sql); 
		    		printResult($result);
		   		}
		    }
		    else
		    {
		    	echo "Unfortunately, no active listings were found with the search criteria.";
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