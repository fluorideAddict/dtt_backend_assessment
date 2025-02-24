<?php

namespace App\Models;

use App\Plugins\Http\Response as Status;
use App\Plugins\Http\Exceptions;

use PDO;
use PDOException;

#[\AllowDynamicProperties]
class Facility extends BaseModel {
	public $id;
	public $name;
	public $location;
	public $tags;
	public $dateCreated;

	public function __construct($facilityData = []) {
		foreach($facilityData as $facilityDataKey => $facilityDataValue){
			if(property_exists($this, $facilityDataKey)){
				$this->$facilityDataKey = $facilityDataValue;
			}
		}
	}

	public function createFacility() {
		$response = (new Status\BadRequest([
			"success" => false, 
			"message" => "Facility creation failed: No facility name given." 
		]));
		if($this->name){
			$facilitiesQuery = "INSERT INTO Facilities (Name, LocationId)     
				VALUES (?, ?)";                                 
			//Location is fixed to Amsterdam (id=1), the Name of the default Location for Facilities that are created after dbSetup.sql is imported         
			$facilitiesQueryBind = [$this->name, "1"];
			try {
				$this->db->beginTransaction();
				$facilitiesQuerySuccess = $this->db->executeQuery($facilitiesQuery, $facilitiesQueryBind);      
				if($facilitiesQuerySuccess){            
					//assign tags if present in request and they exist in the database
					$this->id = $this->db->getlastInsertedId();
					if($this->tags){                     
						//append each tag to be assigned and do it all in one insert query                                                              
						//By removing the IGNORE from INSERT IGNORE below you can force the 
						$facilitiesTagsQuery = "INSERT INTO FacilitiesTags (FacilityId, TagId)";	
						$facilitiesTagsQueryBind = [];                             
						foreach($this->tags as $tagName){
							if($tagName == $this->tags[0]){
								$facilitiesTagsQuery .= " VALUES ";                                                                           
							} else {                        
								$facilitiesTagsQuery .= ", ";
							}
							$facilitiesTagsQuery .= "($this->id, (SELECT Id FROM Tags WHERE Name = ?))";
							$facilitiesTagsQueryBind[] = $tagName;
						}
						$facilitiesTagsQuerySuccess = $this->db->executeQuery($facilitiesTagsQuery, $facilitiesTagsQueryBind);
						if($facilitiesTagsQuerySuccess){
							$this->db->commit();
						}
					} else {
						$this->db->commit();
					}
					//TODO: Location header and created Facility object in response, including tags if present in the request
					$response = (new Status\Created([
						"success" => true,
						"message" => "Facility creation successful.",
						"result" => json_decode($this->readFacility()->getBody(), true)["result"]
					]));
				}
			} catch (PDOException $e) {
				$this->db->rollBack();
				$response = (new Status\InternalServerError([
					"success" => false,
					"message" => "Facility creation failed: One or more fields are invalid. Ensure the supplied tag(s) exist."
				]));
			}		
		}
		return $response;		
	}

	public function readFacility($getAllFacilities = false) {
		$response = (new Status\BadRequest([
			"success" => false,
			"message" => "Reading facility failed: no matches found."
		]));
		$facilityBind = [];
		$facilityQueryWhere = [];
		$facilityQueryHaving = "";
		$facilityQueryWhereArray = [
			"id" => "Facilities.Id = ?",
			"name" => "Facilities.Name LIKE CONCAT('%', ?, '%')"
		];
		$facilityLocationQueryWhereArray = [
			"locationId" => "Locations.Id = ?",
			"city" => "Locations.City LIKE CONCAT('%', ?, '%')",
			"address" => "Locations.Address LIKE CONCAT('%', ?, '%')",
			"zipCode" => "Locations.ZipCode LIKE CONCAT('%', ?, '%')"
		];
		$facilityGroupBy = "GROUP BY Facilities.Id";
		if($this->id){
			$facilityQueryWhere[] = $facilityQueryWhereArray["id"];
			$facilityBind[] = $this->id;
		} else {
			foreach($facilityQueryWhereArray as $key => $value){
				if($this->$key){
					$facilityQueryWhere[] = $value;
					$facilityBind[] = $this->$key;
				}
			}
			if($this->location){
				foreach($facilityLocationQueryWhereArray as $key => $value){
					if($this->location->$key){
						$facilityQueryWhere[] = $value;
						$facilityBind[] = $this->location->$key;
					}
				}
			}
			if($this->tags){
				$tags = explode(",", $this->tags);
				//NOTE: By changing the LIKE below to a =...
				$facilityQueryHaving = "HAVING tagNames LIKE ";
				foreach($tags as $tagsKey => $tagName) {
					if ($tagsKey > 0) {
						//and doing the same for the LIKE below the behaviour of readFacility will change so that it will only return exact matches. The acceptation criterias of User Story 3 state partial matches must for a facility search must be included, but this may interfere the purpose of tags as a way to categorise facilities as looking for facilities with the tag "Test Tag" will also return facilities with the tags "Test Tag 1", "Test Tag 2", etc. 
						$facilityQueryHaving .= " OR LIKE ";
					}
					$facilityQueryHaving .= "CONCAT('%', ?, '%')";
					$facilityBind[] = $tagName;
				}	
			}	
		}
		$facilityQueryWhere = implode(" OR ", $facilityQueryWhere);
		if($facilityQueryWhere || $facilityQueryHaving || $getAllFacilities){
			if($facilityQueryWhere){
				$facilityQueryWhere = "WHERE ($facilityQueryWhere)";	
			};
			$facilityQuery = "SELECT Facilities.Id AS id, Facilities.Name AS name, Facilities.LocationId AS locationId, Locations.City AS city, Locations.Address AS address, Locations.ZipCode AS zipCode, Locations.CountryCode AS countryCode, Locations.PhoneNumber AS phoneNumber, GROUP_CONCAT(DISTINCT Tags.Name) AS tagNames, Facilities.DateCreated AS dateCreated FROM Facilities
				INNER JOIN Locations ON Facilities.LocationId = Locations.Id
				LEFT JOIN FacilitiesTags ON Facilities.Id = FacilitiesTags.FacilityId
				LEFT JOIN Tags ON Tags.Id = FacilitiesTags.TagId " . $facilityQueryWhere . " GROUP BY Facilities.Id " . $facilityQueryHaving;	
			try{
				//$this->db->beginTransaction();
				$facilitySuccess = $this->db->executeQuery($facilityQuery, $facilityBind);
				if($facilitySuccess){
					//Could I skip $this->db->executeQuery and just use the line below? I could be querying the database twice when that is not necessary
					$facilityResult = $this->db->getStatement()->fetchAll(PDO::FETCH_ASSOC);
					$returnResult = [];
					if($facilityResult){
						foreach($facilityResult as $key => $resultRow){
							$facility = new Facility($resultRow);
							$facility->location = new Location([
								"id" => $resultRow["locationId"],
								"city" => $resultRow["city"],
								"address" => $resultRow["address"],
								"zipCode" => $resultRow["zipCode"],
								"countryCode" => $resultRow["countryCode"],
								"phoneNumber" => $resultRow["phoneNumber"]
							]);
							$tags = [];
							if($resultRow["tagNames"]){
								$tags = explode(",", $resultRow["tagNames"]);
								foreach($tags as $index => $tagName){
									$tag = new Tag(["name" => $tagName]);
									$tags[$index] = json_decode($tag->readTag()->getBody(), true)["result"]["name"];
								}
							}
							$facility->tags = $tags;
							$returnResult[] = $facility;
						}
					}
						if($this->id && $returnResult){
						$response = (new Status\Ok([
							"success" => true,
							"result" => $returnResult[0]
						]));
					} else {
						$response = (new Status\Ok([
							"success" => true,
							"result" => $returnResult
						]));
					}
					//$this->db->commit();
				}
			} catch (PDOException $e) {
				//$this->db->rollBack();

				$response = (new Status\InternalServerError([
					"success" => false,
					"message" => "Facility read failed: Invalid parameters."
				]));
			}
		}
		return $response;
	}

	public function updateFacility() {
		//get facility data from facilityToUpdate
		//TODO: Use a select query to give $facility its correct data and also skip the update query if $updateData matches the result of the select query
		$response = (new Status\BadRequest([
			"success" => false, 
			"message" => "Facility update failed: No ID given." 
		]));
		$facilityUpdated = false;
		if($this->id){
			try {
				$this->db->beginTransaction();
				$idCheck = $this->db->executeQuery("SELECT Id FROM Facilities WHERE Id = ?", [$this->id]);
				$idCheckResult = $this->db->getStatement()->fetchAll(PDO::FETCH_ASSOC); 
				if($idCheckResult){
					if($this->name){
						$updateQuery = "UPDATE Facilities
							JOIN Locations on Facilities.LocationId = Locations.Id
							SET Name = ?
							WHERE Facilities.Id = ?";
						$updateQueryBind = [$this->name, $this->id];
						$facilitySuccess = $this->db->executeQuery($updateQuery, $updateQueryBind);
						if($facilitySuccess){
							$facilityUpdated = true;
						}
					}
					if($this->tags){
						//TODO: Insert ignore for duplicate tags? Might be a good idea
						$updateTagsQuery = "INSERT INTO FacilitiesTags (FacilityId, TagId)";
						$updateTagsQueryBind = [];
						foreach($this->tags as $tagName){	
							if($tagName == $this->tags[0]){
								$updateTagsQuery .= " VALUES (?, (SELECT Id FROM Tags WHERE Name = ?))";
							} else {
								$updateTagsQuery .= ", (?, (SELECT Id FROM Tags WHERE Name = ?))";
							}
							array_push($updateTagsQueryBind, $this->id, $tagName);
						}
						$updateTagsQuerySuccess = $this->db->executeQuery($updateTagsQuery, $updateTagsQueryBind);
						if($updateTagsQuerySuccess){
							$facilityUpdated = true;
						}
					}
					if($facilityUpdated){
						$response = (new Status\Ok([
							"success" => true,
							"message" => "Facility successfully updated.",
							"result" => json_decode($this->readFacility()->getBody(), true)["result"]
						]));
					} else {
						$response = (new Status\BadRequest([
							"success" => false,
							"message" => "Facility update failed: No valid properties to update have been supplied."
						]));
					}
					$this->db->commit();
				} else {
					$response = (new Status\NotFound([
						"success" => false,
						"message" => "Facility update failed: No facility found with supplied ID."
					]));
				}
			} catch (PDOException $e) {
				$this->db->rollBack();
				$response = (new Status\InternalServerError([
					"success" => false,
					"message" => "Facility update failed: One or more fields are invalid. Ensure the supplied tag(s) exist and the facility does not already possess said tags."
				]));
			}
		}
		return $response;
	}

	//TODO: Delete function
	public function deleteFacility(){
		$response = (new Status\NotFound([
			"success" => false,
			"message" => "Facility deletion failed: No facility found with the supplied ID."
		]));

		$idCheck = $this->db->executeQuery("SELECT Id FROM Facilities WHERE Id = ?", [$this->id]);
		$idCheckResult = $this->db->getStatement()->fetchAll(PDO::FETCH_ASSOC); 
		if($idCheckResult){
			$query = "DELETE FROM Facilities WHERE Id = ?";
			$bind = [$this->id];
			try {
				$this->db->beginTransaction();
				$success =$this->db->executeQuery($query, $bind);
				if ($success) {
					$response = (new Status\NoContent());
					$this->db->commit();
				}
			} catch (PDOException $e) {
				$this->db->rollBack();
				$response = (new Status\InternalServerError([
					"success" => false,
					"message" => "Facility deletion failed: An exception has occured."
				]));
			}
		}
		return $response;
	}
}
