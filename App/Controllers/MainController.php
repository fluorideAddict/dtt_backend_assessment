<?php

//This controller contains all the functions necessary for setting and getting data to/from the database

namespace App\Controllers;
//TODO: docker container

//include_once "/opt/lampp/htdocs/web_backend_test_catering_api/App/Models/Facility.php"
//
use App\Models\Facility;
use App\Models\Location;
use App\Models\Tag;

use App\Plugins\Http\Response as Status;	// For HTTP status codes
use App\Plugins\Http\Exceptions;		// Also for HTTP status codes? Present in IndexController.php so it is also used here for good measure.
use PDO;					// Necessary for fetching results of sql statements as associative arrays
USE PDOException;

//The below line mutes the warning for depracation of dynamic properties which causes syntax errors when displaying json responses in the browser
#[\AllowDynamicProperties]
class MainController extends BaseController {
	//Create functions
	//Create a facility
	public function createFacility() {
		//new
		$response = (new Status\BadRequest([
			"success" => false, 
			"message" => "Facility creation failed: Bad request."
		]));	

		$requestBodyJson = file_get_contents("php://input");
		$requestBody = json_decode($requestBodyJson, true);
		if($requestBody && isset($requestBody["createData"])){
			//pass the id of the facility you would like to update in facilityToUpdate
			$facility = new Facility($requestBody["createData"]);
			$response = $facility->createFacility();
		}
		$response->send();
	}
	//This function creates a Tag with the "name"
	public function createTag() {
		//put something in place to avoid duplicate entries?
		$response = (new Status\BadRequest([
			"success" => false,
			"message" => "Tag creation failed: Ensure a name is provided and the tag does not already exist."
		]));
		$requestJson = file_get_contents("php://input");
		$requestData = json_decode($requestJson, true);
		if($requestData && isset($requestData["createData"])){
			$createData = $requestData["createData"];
			$tag = new Tag($createData);
			$response = $tag->createTag();
		}
		$response->send();
	}
	//Read functions
	//This function returns a facility from Facilities where the Name exactly matches the given $facilityName as a JSON object
	public function getFacility($id = 0){
		$response = (new Status\BadRequest([
			"success" => false,
			"message" => "Facility read failed: invalid or missing parameters."
		]));
		if ($id) {
			$facility = new Facility(["id" => $id]);
			$response = $facility->readFacility();	
		} elseif($_GET){
			//$searchParams = ["facilityId", "name", "tags", "locationId", "city", "address", "zipCode"];
			$facilityData = [
				//"id" => $_GET["id"] ?? null,
				"name" => $_GET["name"] ?? null,
				"tags" => $_GET["tags"] ?? null,
				"location" => new Location([
					"locationId" => $_GET["locationId"] ?? null,
					"city" => $_GET["city"] ?? null,
					"address" => $_GET["address"] ?? null,
					//TODO: Filter out spaces from the zipcode parameter if present
					"zipCode" => $_GET["zipCode"] ?? null
				])
			];
			$facility = new Facility($facilityData);
			$response = $facility->readFacility();	
		} else {
			$facility = new Facility();
			$response = $facility->readFacility(true);
		}
		$response->send();
	}
	
	public function getTag($id = 0){
		$response = (new Status\BadRequest([
			"success" => false,
			"message" => "Tag read failed: invalid or missing parameters."
		]));
		if ($id) {
			$tag = new Tag(["id" => $id]);
			$response = $tag->readTag();	
		} elseif($_GET){
			$tagData = [
				//"id" => $_GET["id"] ?? null,
				"name" => $_GET["name"] ?? null
			];
			$tag = new Tag($tagData);
			$response = $tag->readTag();	
		} else {
			$tag = new Tag();
			$response = $tag->readTag(true);
		}
		$response->send();

	}
	//Update functions
	//Update the facility with all supplied (valid) properties
	public function updateFacility($id = 0){
		$response = (new Status\BadRequest([
			"success" => false, 
			"message" => "Facility update failed: Bad request. Ensure the facility with the supplied ID exists."
		]));
		if($id){
			$requestBodyJson = file_get_contents("php://input");
			$requestBody = json_decode($requestBodyJson, true);
			if($requestBody && isset($requestBody["updateData"])){
				//pass the id of the facility you would like to update in facilityToUpdate
				//$updateOnFacilityId = $requestBody["updateOnFacilityId"];
				$updateData = $requestBody["updateData"];
				$updateData["id"] = $id;
				$facility = new Facility($updateData);
				//$facility->id = $updateOnFacilityId;
				$response = $facility->updateFacility();
			}
		}
		$response->send();
	}	

	public function updateTagOnId($id){
		$response = (new Status\BadRequest([
			"success" => false,
			"message" => "Tag update failed: ensure a tag with the supplied ID exists."
		]));
		if($id){
			$requestBodyJson = file_get_contents("php://input");
			$requestBody = json_decode($requestBodyJson, true);
			if($requestBody && isset($requestBody["updateData"])){
				$updateData = $requestBody["updateData"];
				$tag = new Tag($updateData);
				$response = $tag->updateTag("id", $id);
			}
		}	
		$response->send();
	}

	/*public function updateTagOnName($name){
		$response = (new Status\BadRequest([
			"success" => false,
			"message" => "Tag update failed: ensure a tag with the supplied ID exists."
		]));
		if ($name) {
			$requestBodyJson = file_get_contents("php://input");
			$requestBody = json_decode($requestBodyJson, true);
			if($requestBody && isset($requestBody["updateData"])){
				$name = str_replace("_", " ", $name);
				$updateData = $requestBody["updateData"];
				$tag = new Tag($updateData);
				$response = $tag->updateTag("name", $name);
			}
		}
		$response->send();
	}*/

	//Delete functions
	//Deletes all references to $facilityName from the FacilitiesTags table and deletes the facility from the Facilities table
	public function deleteFacility($facilityId){
		$response = (new Status\BadRequest([
			"success" => false,
			"message" => "Facility deletion failed: No valid ID was given."
		]));
		if($facilityId) {
			$facility = new Facility(["id" => $facilityId]);
			$response = $facility->deleteFacility();			
		}
		$response->send();
	}
	//Deletes all references to $tagName from the FacilitiesTags table and deletes the tag from the Tags table
	public function deleteTag($tagId){
		$response = (new Status\BadRequest([
			"success" => false,
			"message" => "Tag deletion failed: invalid or missing parameters."
		]));
		if($tagId) {
			$tag = new Tag(["id" => $tagId]);
			$response = $tag->deleteTag();		
		}
		$response->send();
	}

	public function deleteTagFromFacility($facilityId, $tagId){
		$response = (new Status\BadRequest([
			"success" => false,
			"message" => "Tag deletion failed: invalid or missing parameters."
		]));
		if($facilityId && $tagId) {
			$facility = new Facility(["id" => $facilityId]);
			$response = $facility->deleteTagFromFacility($tagId);
		}
		$response->send();
	}
}

