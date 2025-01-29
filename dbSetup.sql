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
	Tag varchar(255) NOT NULL,
	DateCreated DATETIME NOT NULL,
	FOREIGN KEY (Location) REFERENCES Locations(City),
	FOREIGN KEY (Tag) REFERENCES Tags(Name)
);

