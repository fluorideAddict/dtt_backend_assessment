CREATE DATABASE cateringDB;
USE cateringDB;

CREATE TABLE Locations(
	Id int NOT NULL AUTO_INCREMENT,
	City varchar(255) NOT NULL,
	Address varchar(255) NOT NULL,
	ZipCode varchar(255) NOT NULL,
	CountryCode varchar(255) NOT NULL,
	PhoneNumber varchar(255) NOT NULL,
	PRIMARY KEY (Id)
);

CREATE TABLE Tags(
	Id int NOT NULL AUTO_INCREMENT,
	Name varchar(255) NOT NULL UNIQUE,
	PRIMARY KEY (Id)
);

CREATE TABLE Facilities(
	Id int NOT NULL AUTO_INCREMENT,
	Name varchar(255) NOT NULL,
	LocationId int NOT NULL,
	DateCreated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (Id),
	FOREIGN KEY (LocationId) REFERENCES Locations(Id)
);

CREATE TABLE FacilitiesTags(
	FacilityId int NOT NULL,
	TagId int NOT NULL,
	PRIMARY KEY (FacilityId, TagId),
	FOREIGN KEY (FacilityId) REFERENCES Facilities(Id) ON DELETE CASCADE,
	FOREIGN KEY (TagId) REFERENCES Tags(Id) ON DELETE CASCADE
);

/* populate locations table */
INSERT INTO Locations (City, Address, ZipCode, CountryCode, PhoneNumber)
VALUES ("Amsterdam", "Sint Nicolaasstraat 9", "1012NJ", "31", "020 331 5848");

/* populate facilities table */
/*INSERT INTO Facilities ()
VALUES ();*/

/* populate tags table */
/*INSERT INTO Tags ()
VALUES ();*/
