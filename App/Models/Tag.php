<?php

namespace App\Models;

use App\Plugins\Http\Response as Status;                                                              
use App\Plugins\Http\Exceptions;

use PDO;
use PDOException;

#[\AllowDynamicProperties]
class Tag extends BaseModel {
	public $id;
	public $name;

	public function __construct($tagData = []){
                foreach($tagData as $tagDataKey => $tagDataValue){        
			$this->$tagDataKey = $tagDataValue;
		}
	}

	public function createTag(){
		$response = (new Status\BadRequest([
			"success" => false,
			"message" => "Tag creation failed: Ensure a name is provided and the tag does not already exist."
		]));
		if($this->name){
				$query = "INSERT INTO Tags (Name)                     
					VALUES (?)";                                  
				$bind = [$this->name];
			try {
				$this->db->beginTransaction();                        
				$success = $this->db->executeQuery($query, $bind);    
				if ($success) {                                        
					//TODO: Location header and created Tag in response	
					$response = (new Status\Created([
						"success" => true,                    
						"message" => "Tag successfully created."
					]));
					$this->db->commit();  
				}                                                     
			} catch (PDOException $e) {
				$this->db->rollBack();
				//TODO: Response
				$response = (new Status\InternalServerError([
					"success" => false,
					"message" => "Tag creation failed: One or more fields are invalid. Tag may already exist."
				]));
			}
		}
		return $response;	
	}

	public function readTag($getAllTags = false){
		$response = (new Status\BadRequest([
			"success" => false,
			"message" => "Reading tag failed: no matches found."
		]));
		$tagQuery = "SELECT Id as id, Name as name FROM Tags";
		$tagBind = [];
		if($this->id){
			$tagQuery .= " WHERE Id = ?";
			$tagBind = [$this->id];
		} elseif(!$getAllTags){ 
			$tagQuery .= " WHERE Id = ? OR Name LIKE CONCAT('%', ?, '%')";
			$tagBind = [
				$this->id,
				$this->name
			];
		}
		try {
			//$this->db->beginTransaction();
			$tagQuerySuccess = $this->db->executeQuery($tagQuery, $tagBind);
			if ($tagQuerySuccess) {
				$queryResult = $this->db->getStatement()->fetchAll(PDO::FETCH_ASSOC);
				$returnResult = [];
				if($queryResult){
					foreach($queryResult as $queryResultRow){
						$returnResult[] = new Tag([
							"id" => $queryResultRow["id"],
							"name" => $queryResultRow["name"]
						]);
					}
				}

				if($getAllTags){
					$response = (new Status\Ok([
						"success" => true,
						"result" => $returnResult
					]));
				} else {
					$response = (new Status\Ok([
						"success" => true,
						"result" => $returnResult[0]
					]));
				}
				//$this->db->commit();
			}
		} catch (PDOException $e) {
			$response = (new Status\InternalServerError([
				"success" => false,
				"message" => "Reading facility failed."
			]));
			//$this->db->rollBack();
		}
		return $response;
	}
	
	public function updateTag($onIdOrName, $idOrName){
		$response = (new Status\BadRequest([
			"success" => false,
			"message" => "Tag update failed: Invalid ID or tag name given."
		]));

		if($this->name) {
			try {
				$this->db->beginTransaction();
				$tagCheckQuery;
				$tagCheckQueryBind = [$idOrName];
				//TODO: Remove the ability to update tag by ID? It is rather unecessary
				$updateQuery = "UPDATE Tags
						SET Name = ?";
				$updateQueryBind = [$this->name, $idOrName];
				if ($onIdOrName == "name") {
					$tagCheckQuery = "SELECT Name FROM Tags WHERE Name = ?";
					$updateQuery .= " WHERE Name = ?";
				} /*elseif ($onIdOrname == "id") {
					$tagCheckQuery = "SELECT Id FROM Tags WHERE Id = ?";
					$updateQuery .= " WHERE Id = ?";
				}*/
				$tagCheck = $this->db->executeQuery($tagCheckQuery, $tagCheckQueryBind);
				$tagCheckResult = $this->db->getStatement()->fetchAll(PDO::FETCH_ASSOC);
				if($tagCheckResult){
					$updateSuccess = $this->db->executeQuery($updateQuery, $updateQueryBind);
					if($updateSuccess){
						$response = (new Status\Ok([
							"success" => true,
							"message" => "Tag update successful.",
							"oldTagName" => $idOrName,
							"newTagName" => $this->name
						]));
						$this->db->commit();
					}
				}
		
			} catch (PDOException $e) {
				$this->db->rollBack();
			}
		}
		return $response;
	}

	public function deleteTag(){
		$response = (new Status\NotFound([
			"success" => false,
			"message" => "Tag deletion failed: No tag found with the supplied ID."
		]));	

		$idCheck = $this->db->executeQuery("SELECT Id FROM Tags WHERE Id = ?", [$this->id]);
		$idCheckResult = $this->db->getStatement()->fetchAll(PDO::FETCH_ASSOC);	
		if($idCheckResult){
			$query = "DELETE FROM Tags WHERE Id = ?";
			$bind = [$this->id];
			try {
				$this->db->beginTransaction();
				$success = $this->db->executeQuery($query, $bind);
				if ($success) {
					$response = (new Status\NoContent());
					$this->db->commit();
				}
			} catch (PDOException $e) {
				$this->db->rollBack();
			}
		}
		return $response;
	}
}
