<?php

namespace Models;

/**
 * Record Object Hashable for Collections of the database
 * 
 * @author Savio Resende <savio@savioresende.com.br>
 */

class Record implements \JsonSerializable {
	
	protected $id;

	protected $permissions;

	protected $type;

	protected $revision_hash;

	protected $address;

	protected $file_content;

	public function __construct(){

		$this->file_content = new \stdClass;

	}

	// TODO: this can be a specific trait for custom toString
	// public $string_attribute;

	// ############################## getters and setters ##############################

	public function setId( $id ){
		$this->id = $id;
	}

	public function getID(){
		return $this->id;
	}

	// --

	public function setPermissions( $permissions ){
		$this->permissions = $permissions;
	}

	public function getPermissions(){
		return $this->permissions;
	}

	// --

	public function setType( $type ){
		$this->type = $type;
	}

	public function getType(){
		return $this->type;
	}

	// --

	public function setRevisionHash( $revision_hash ){
		$this->revision_hash = $revision_hash;
	}

	public function getRevisionHash(){
		return $this->revision_hash;
	}

	// --

	public function setAddress( $address ){
		$this->address = $address;
	}

	public function getAddress(){
		return $this->address;
	}

	// -- file_content --

	public function setFileContent( $file_content ){
		foreach ($file_content as $key => $value) {
			$this->file_content->{$key} = $value;
		}
	}

	public function getFileContent(){
		return $this->file_content;
	}

	public function setFileTimestamp( $timestamp ){
		$this->file_content->timestamp = $timestamp;
	}

	public function getFileTimestamp(){
		return $this->file_content->timestamp;
	}

	public function setFileUpdatedAt( $timestamp ){
		$this->file_content->updated_at = $timestamp;
	}

	public function getFileUpdatedAt(){
		return $this->file_content->updated_at;
	}

	// ############################## getters and setters ##############################

	// TODO: this can be a specific trait for custom toString
	// public function __toString()
	// {
	// 	$attribute = $this->string_attribute;
	// 	return $this->{$attribute};
	// }

	public function jsonSerialize(){
		return [
			'id' => $this->id,
			'permissions' => $this->permissions,
			'type' => $this->type,
			'revision_hash' => $this->revision_hash,
			'address' => $this->address,
			'file_content' => $this->file_content
		];
	}

	/**
	 * @todo implement the logic (OR, AND, ...)
	 * @todo the OR is not complete
	 */
	public function multipleParamsMatch( $params, $logic = [] ){

		// --------------------------------------------------------
		// AND for all logics -------------------------------------
		// --------------------------------------------------------
		if( empty($logic) ){
			foreach ($params as $key => $attribute) {
				if( 
					(
						isset($this->file_content->{$key}) && (
							(
								$this->file_content->{$key} == "id"
								&& $this->valueEqual( $key, $attribute )
							) || $this->stringMatch( $key, $attribute )
						)
					) || (
						isset($this->{$key})
						&& $this->{$key} == $attribute
					)
				) 
					continue;
				else
					return false;
				
			}
		}
		// --------------------------------------------------------

		// --------------------------------------------------------
		// OR for all logics -------------------------------------
		// --------------------------------------------------------
		if( !empty($logic) ){
			$resultant = array_filter($params, function($attribute, $key) use ($params) {
				return isset($this->file_content->{$key}) && (
					(
						$this->file_content->{$key} == "id"
						&& $this->valueEqual( $key, $attribute )
					) || (
						$this->file_content->{$key} != "id"
						&& $this->stringMatch( $key, $attribute )
					)
				);
			}, ARRAY_FILTER_USE_BOTH);

			return !empty($resultant);
		}
		// --------------------------------------------------------

		return true;

	}

    /**
     * 
     */
    public function stringMatch( $param, $value ){
    	return (
    		isset($this->file_content->{$param})
        	&& strstr($this->file_content->{$param}, $value) !== false
        );
    }

    /**
     * 
     */
    public function valueEqual( $param, $value ){
    	return (
        	isset($this->file_content->{$param})
            && $this->file_content->{$param} != $value
        );
    }

	/**
	 * This method loads the Structure 1. This structure is 
	 * the structure that the Coyl/Git returns in the line,
	 * which is the same as running the git CLI.
	 * 
	 * @param String $records_row
	 * @param Bool $is_db
	 * @return $this
	 */
	public function loadRowStructure1( $records_row, $is_db ){
		$records_row = preg_split('/\s+/', $records_row);

		$records_row = array_filter($records_row);
		
		if( empty($records_row) )
			return $this;

		if( $is_db )
			$this->setId( $this->getIdOfAsset($records_row[3]) );

		$this->setPermissions( $records_row[0] );
		$this->setType( $records_row[1] );
		$this->setRevisionHash( $records_row[2] );
		$this->setAddress( $records_row[3] );

		return $this;
	}

	/**
	 * Load structure for the filesystem search
	 * 
	 * @param String $full_database_address (directory tree)
	 * @param String $records_row
	 * @return $this
	 */
	public function loadRowStructureSimpleDir( $full_database_address, $records_row ){
		if( empty($records_row) )
			return $this;

		if( $full_database_address[strlen($full_database_address) - 1] != "/" )
			$full_database_address = $full_database_address . "/";

		$records_address = $full_database_address . $records_row;
		
		$permissions = substr(sprintf('%o', fileperms($records_address)), -4);
		$this->setId($records_row);
		$this->setPermissions( $permissions );
		$this->setAddress( $records_address );
		$this->setFileTimestamp( filemtime( $records_address ) );
		$this->setFileUpdatedAt( gmdate("Y-m-d H:i:s", $this->getFileTimestamp()) );

		return $this;
	}

	/**
	 * Load structure from cache, which is basic Array.
	 * 
	 * @todo to be deleted soon (maybe)
	 * @param Array $record_row
	 * @return void
	 */
	public function loadRecordCacheStructure( $record_row ){
		$this->setId( $record_row['id'] );
		$this->setPermissions( $record_row['permissions'] );
		$this->setType( $record_row['type'] );
		$this->setRevisionHash( $record_row['revision_hash'] );
		$this->setAddress( $record_row['address'] );
		$this->setFileContent( (object) $record_row['file_content'] );
	}

	/**
	 * Return the Id of the physical address
	 * 
	 * @return Int
	 */
	public function getIdOfAsset( $address ){

		$address_exploded = explode('/', $address);

		$asset_name = end($address_exploded);

		$id = preg_replace("/[^\d]/", "", $asset_name);

		return $id;

	}

}