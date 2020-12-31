/* Initialize tables and instances */



drop table Account_Admin;
drop table Account_User;
drop table CreditCard;
drop table TransactionRecord_Records;
drop table Game;
drop table Accessory;
drop table Owned_by;
drop table Listings_Post CASCADE CONSTRAINTS;
drop table Company CASCADE CONSTRAINTS;
drop table Item CASCADE CONSTRAINTS;
drop table Account CASCADE CONSTRAINTS;


CREATE TABLE Account(
	username CHAR(30),
	Password CHAR(30),
	Recovery CHAR(30),
	member_since DATE,
	phone CHAR(30),
	PRIMARY KEY (username));
 
grant select on Account to public;
 
CREATE TABLE Account_Admin(
	username CHAR(30),
	adminID CHAR(30),
	PRIMARY KEY (username),
	FOREIGN KEY (username) REFERENCES Account(username) ON DELETE CASCADE);
 
grant select on Account_Admin to public;

CREATE TABLE Account_User(
    username CHAR(30),
	wallet_amount INTEGER,
	Rating INTEGER,
	PRIMARY KEY (username),
	FOREIGN KEY (username)
	REFERENCES Account(username) ON DELETE CASCADE,
	CHECK (Rating >= 1 And Rating <= 5));
 	
grant select on Account_User to public;

CREATE TABLE CreditCard(
	Username CHAR(30),
	card INTEGER,
	ExpDate CHAR(30),
	CVC INTEGER,	
	PRIMARY KEY (card),
	FOREIGN KEY (Username) 
		REFERENCES Account(Username)	
		ON DELETE CASCADE);

grant select on CreditCard to public;
 
CREATE TABLE Listings_Post(
    username CHAR(30) not NULL,
    listingID CHAR(30),
    Active CHAR(30),
    Selling CHAR(30),
    Title CHAR(30),
	Price INTEGER,
	Location CHAR(30),
	DatePosted DATE,
	PRIMARY KEY (listingID),
	FOREIGN KEY (username)
		REFERENCES Account(username)
		ON DELETE CASCADE);

grant select on Listings_Post to public;

CREATE TABLE TransactionRecord_Records(	
    username CHAR(30),    
    TransactionID CHAR(30) NOT NULL,
	DateSold DATE,
	listingID CHAR(30),
	PRIMARY KEY (TransactionID),
	FOREIGN KEY (username)
		REFERENCES Account(username)
		ON DELETE CASCADE,
	FOREIGN KEY (listingID) 
		REFERENCES Listings_Post(listingID)
		ON DELETE CASCADE);

grant select on TransactionRecord_Records to public;


CREATE TABLE Company(
	CompanyName CHAR(30),
	location CHAR(30),
	founded DATE,
	PRIMARY KEY (CompanyName)
	);
		
grant select on Company to public;

CREATE TABLE Item(
    itemID CHAR(30),
    listingID CHAR(30),    
    ItemName CHAR(30),  
    CompanyName CHAR(30),        
    platform CHAR(30),
    MSRP INTEGER,
    PRIMARY KEY (itemID),
    FOREIGN KEY (CompanyName) 
		REFERENCES Company(CompanyName)
		ON DELETE CASCADE,
	FOREIGN KEY (listingID) 
		REFERENCES Listings_Post(listingID)
		ON DELETE CASCADE);

grant select on Item to public;

CREATE TABLE Game(
    itemID CHAR(30),
    release_date CHAR(20),
    genre CHAR(30),
    franchise CHAR(30),
    players INTEGER,
    PRIMARY KEY (itemID),
    FOREIGN KEY (itemID)
        REFERENCES Item(itemID)
		ON DELETE CASCADE);

grant select on Game to public;

CREATE TABLE Accessory(
    itemID CHAR(30),
    Colour CHAR(30),
    Type CHAR(30),
    PRIMARY KEY (itemID),
    FOREIGN KEY (itemID)
        REFERENCES Item(itemID)
		ON DELETE CASCADE);
	
grant select on Accessory to public;


CREATE TABLE Owned_by(
	parent_company CHAR(30),
	child_company CHAR(30),
	PRIMARY KEY (parent_company, child_company),
	FOREIGN KEY (parent_company) 
    	REFERENCES Company(CompanyName)
		ON DELETE CASCADE,
	FOREIGN KEY (child_company) 
    	REFERENCES Company(CompanyName)
		ON DELETE CASCADE);
 
grant select on Owned_by to public;









