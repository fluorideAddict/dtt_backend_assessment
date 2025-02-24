<?php

namespace App\Models;

#[\AllowDynamicProperties]
class Location extends BaseModel {
	public $id;
	public $city;
	public $address;
	public $zipCode;
	public $countryCode;
	public $phoneNumber;

	public function __construct($locationData){
                foreach($locationData as $locationDataKey => $locationDataValue){            
			$this->$locationDataKey = $locationDataValue;
		}
	}
}
