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
INSERT INTO Locations (City, Address, ZipCode, CountryCode, PhoneNumber) VALUES ("Amsterdam", "Sint Nicolaasstraat 9", "1012NJ", "31", "0203315848");
INSERT INTO Locations (City, Address, ZipCode, CountryCode, PhoneNumber) VALUES ("Amsterdam", "Van Woustraat 69-hs", "1074AD", "31", "0206645771");
INSERT INTO Locations (City, Address, ZipCode, CountryCode, PhoneNumber) VALUES ("Tilburg", "Veemarktstraat 44", "5038CV", "31", "0314609500");
INSERT INTO Locations (City, Address, ZipCode, CountryCode, PhoneNumber) VALUES ("Den Haag", "Binnenhof 17", "2513AA", "31", "0201234567");

/* populate facilities table */
INSERT INTO Facilities (Name, LocationId) VALUES ("Headquarters", 1);
INSERT INTO Facilities (Name, LocationId) VALUES ("Secondary Office", 2);
INSERT INTO Facilities (Name, LocationId) VALUES ("Factory", 3);
INSERT INTO Facilities (Name, LocationId) VALUES ("Call Center", 4);

/* populate tags table */
INSERT INTO Tags (Name) VALUES ("Customer Support");
INSERT INTO Tags (Name) VALUES ("Storage");
INSERT INTO Tags (Name) VALUES ("Parking Space");
INSERT INTO Tags (Name) VALUES ("Elevator");
INSERT INTO Tags (Name) VALUES ("Coffee Machine");
INSERT INTO Tags (Name) VALUES ("Security");

/* give facilities some tags */
INSERT INTO FacilitiesTags (FacilityId, TagId) VALUES (1, 4);
INSERT INTO FacilitiesTags (FacilityId, TagId) VALUES (1, 5);
INSERT INTO FacilitiesTags (FacilityId, TagId) VALUES (3, 2);
INSERT INTO FacilitiesTags (FacilityId, TagId) VALUES (3, 3);
INSERT INTO FacilitiesTags (FacilityId, TagId) VALUES (3, 4);
INSERT INTO FacilitiesTags (FacilityId, TagId) VALUES (4, 1);
INSERT INTO FacilitiesTags (FacilityId, TagId) VALUES (4, 5);
