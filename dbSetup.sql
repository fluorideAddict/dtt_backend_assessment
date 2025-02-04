CREATE DATABASE cateringDB;
USE cateringDB;

CREATE TABLE Locations(
	City varchar(255) NOT NULL PRIMARY KEY,
	Address varchar(255) NOT NULL,
	ZipCode varchar(255) NOT NULL,
	CountryCode varchar(255) NOT NULL,
	PhoneNumber varchar(255) NOT NULL
);

CREATE TABLE Tags(
	Name varchar(255) NOT NULL PRIMARY KEY
);

CREATE TABLE Facilities(
	Name varchar(255) NOT NULL PRIMARY KEY,
	Location varchar(255) NOT NULL,
	Tags varchar(255) DEFAULT '',
	DateCreated DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
	FOREIGN KEY (Location) REFERENCES Locations(City)
);

INSERT INTO Locations (City, Address, ZipCode, CountryCode, PhoneNumber)
VALUES ("Amsterdam", "Sint Nicolaasstraat 9", "1012 NJ", "31", "020 331 5848");
