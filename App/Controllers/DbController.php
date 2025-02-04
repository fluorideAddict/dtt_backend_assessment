<?php

//This controller contains all the functions necessary for POSTing and GETting data to/from the database

namespace App\Controllers;

use App\Plugins\Http\Response as Status;
use App\Plugins\Http\Exceptions;
use PDO;

#[\AllowDynamicProperties]
class DbController extends BaseController {
	//Create a facility
	//TODO: automatically create and assign the requested tag if it does not already exist
	public function createFacility() {
		$facilityJson = file_get_contents("php://input");
		$facilityData = json_decode($facilityJson, true);
		if($facilityData !== null){
			$facilityName = $facilityData["name"];
			if($facilityName !== null){
				$query = "INSERT INTO Facilities (Name, Location)
					VALUES (?, ?)";
				//Location is fixed to Amsterdam, the Name of the default Location for Facilities that are created after dbSetup.sql is imported
				$bind = [$facilityName, "Amsterdam"];
				$success = $this->db->executeQuery($query, $bind);
				if ($success){
					(new Status\Created(['message' => 'Facility successfully created.']))->send();
					return;
				}
			}
		}
		//if facilityJson is empty, facilityName was not included in facilityJson or the query was unsuccessful then return a status 400
		//TODO: Return an error 400 upon trying to create a duplicate facility
		(new Status\BadRequest(['message' => 'Facility creation failed.']))->send();	
	}

	public function createTag($tagName) {
		//put something in place to avoid duplicate entries?
		$query = "INSERT INTO Tags (Name)
			VALUES (?)";
		$bind = [$tagName];
		$success = $this->db->executeQuery($query, $bind);
		if($success){
			(new Status\Ok(['message' => 'Tag successfully created!']))->send();
		}
		//return $success;
		//
	}
	//Read functions
	/*public function selectTag($tagName) {
		$query = "SELECT * FROM Tags WHERE Name = ?";
		print("test");
		print($tagName);
		$bind = ["s", $tagName];
		$this->db->executeQuery($query, $bind);
	}*/

	public function readFacility($name){
		if($name !== null){
			$query = "SELECT * FROM Facilities WHERE Name = ?";
			$bind = [$name];
			$success = $this->db->executeQuery($query, $bind);
			if($success){
				$result = $this->db->getStatement()->fetch(PDO::FETCH_ASSOC);
				$resultJson = json_encode($result);
				(new Status\Ok($resultJson))->send();
				return;
			}
		}
		(new Status\BadRequest(['message' => 'Reading facility failed.']))->send();
	}
	//Update functions
	/*
	public function updateTag(oldTagName, newTagName) {
		
	}
	//Delete functions
	//Select facility with specific name, delete it
	public function deleteFacility(Name) {

	}
	public function deleteTags(tagName){
	
	}
	*/
}

