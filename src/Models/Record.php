<?php

namespace Models;

use Exception;
use Models\Abstraction\GitDAO;
use stdClass;
use JsonSerializable;

/**
 * Record Object Hashable for Collections of the database
 */
class Record implements JsonSerializable
{
    /** @var string|null */
    protected $id;

    /** @var int|null */
    protected $permissions;

    /** @var string|null */
    protected $type;

    /** @var string|null */
    protected $revision_hash;

    /** @var string|null */
    protected $address;

    /** @var stdClass */
    protected $file_content;

    /** @var bool */
    protected $case_sensitive;

    public function __construct()
    {
        $config = config()['settings'];

        $this->file_content = new stdClass;
        $this->case_sensitive = $config['case_sensitive'];
    }

    // TODO: this can be a specific trait for custom toString
    // public $string_attribute;

    // ############################## getters and setters ##############################

    /**
     * @param string $id
     *
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getID()
    {
        return $this->id;
    }

    // --

    /**
     * @param int $permissions
     *
     * @return void
     */
    public function setPermissions(int $permissions): void
    {
        $this->permissions = $permissions;
    }

    /**
     * @return int|null
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    // --

    /**
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    // --

    /**
     * @param string $revision_hash
     *
     * @return void
     */
    public function setRevisionHash(string $revision_hash): void
    {
        $this->revision_hash = $revision_hash;
    }

    /**
     * @return string|null
     */
    public function getRevisionHash()
    {
        return $this->revision_hash;
    }

    // --

    /**
     * @param string $address
     *
     * @return void
     */
    public function setAddress(string $address): void
    {
        $this->address = trim($address);
    }

    /**
     * @return string|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    // -- file_content --

    /**
     * @param array $file_content
     *
     * @return void
     */
    public function setFileContent(array $file_content): void
    {
        foreach ($file_content as $key => $value) {
            $this->file_content->{$key} = $value;
        }
    }

    /**
     * @return stdClass
     */
    public function getFileContent(): stdClass
    {
        return $this->file_content;
    }

    /**
     * @param string $timestamp
     */
    public function setFileTimestamp(string $timestamp): void
    {
        $this->file_content->timestamp = $timestamp;
    }

    /**
     * @return string|null
     */
    public function getFileTimestamp()
    {
        return $this->file_content->timestamp;
    }

    /**
     * @param string $timestamp
     *
     * @return void
     */
    public function setFileUpdatedAt(string $timestamp): void
    {
        $this->file_content->updated_at = $timestamp;
    }

    /**
     * @return string|null
     */
    public function getFileUpdatedAt()
    {
        return $this->file_content->updated_at;
    }

    // ############################## getters and setters ##############################

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
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
     * This match is for JSON assets
     *
     * @todo the OR is not complete
     * @todo implement the logic (OR, AND, ...)
     *
     * @param array $params
     * @param array $logic
     *
     * @return bool
     */
    public function multipleParamsMatch($params, $logic = [])
    {
        // --------------------------------------------------------
        // AND for all logics -------------------------------------
        // --------------------------------------------------------
        if (empty($logic)) {
            foreach ($params as $key => $attribute) {
                if (
                    (
                        isset($this->file_content->{$key}) && (
                            (
                                $this->file_content->{$key} === "id"
                                && $this->valueEqual($key, $attribute)
                            ) || (
                                isset($this->file_content->{$key})
                                && $this->stringMatch($this->file_content->{$key}, $attribute))
                        )
                    ) || (
                        isset($this->{$key})
                        && $this->stringMatch($this->{$key}, $attribute)
                    )
                ) {
                    continue;
                } else {
                    return false;
                }
            }
        }
        // --------------------------------------------------------

        // --------------------------------------------------------
        // OR for all logics --------------------------------------
        // --------------------------------------------------------
        if (!empty($logic)) {
            $resultant = array_filter($params, function ($attribute, $key) use ($params) {
                return isset($this->file_content->{$key}) && (
                        (
                            $this->file_content->{$key} == "id"
                            && $this->valueEqual($key, $attribute)
                        ) || (
                            $this->file_content->{$key} != "id"
                            && $this->stringMatch($this->file_content->{$key}, $attribute)
                        )
                    );
            }, ARRAY_FILTER_USE_BOTH);

            return !empty($resultant);
        }
        // --------------------------------------------------------

        return true;
    }

    /**
     * This is meant for raw data.
     *
     * @param array $params
     *
     * @return bool
     *
     * @throws Exception
     */
    public function titleContentMatch(array $params): bool
    {
        $match = true;

        if (isset($params['id'])) {
            $match = $match && $this->getAddress() === $params['id'];
            unset($params['id']);
        }

        if (isset($params['address'])) {
            $match = $match && $this->getAddress() === $params['address'];
            unset($params['address']);
        }

        if (isset($params['content']) && property_exists($this->getFileContent(), 'content')) {
            $match = $match && $this->stringMatch($params['content'], $this->getFileContent()->content);
            unset($params['content']);
        }

        if (count($params) > 0) {
            throw new Exception('Fields not known to this type of record: ' . implode(', ', array_keys($params)) . '.');
        }

        return $match;
    }

    /**
     * @param string $param
     * @param string $value
     *
     * @return bool
     */
    public function stringMatch(string $param, string $value)
    {
        if ($this->case_sensitive) {
            $match_string = strstr($param, $value) !== false;
        } else {
            $match_string = strstr(strtolower($value), strtolower($param)) !== false;
        }

        return $match_string;
    }

    /**
     * @param string $param
     * @param string $value
     *
     * @return bool
     */
    public function valueEqual(string $param, string $value): bool
    {
        if (!property_exists($this->file_content, $param)) {
            return false;
        }

        return $this->file_content->{$param} == $value;
    }

    /**
     * This method loads the Structure 1 (as a line result of `git ls-tree`
     * command).
     *
     * @param string $records_row
     * @param bool $is_db
     *
     * @return $this
     */
    public function loadRowStructure1(string $records_row, bool $is_db)
    {
        $records_row = preg_split('/\s+/', $records_row);

        $records_row = array_filter($records_row);

        if (empty($records_row)) {
            return $this;
        }

        if ($is_db) {
            $this->setId($this->getIdOfAsset($records_row[3]));
        }

        $this->setPermissions($records_row[0]);
        $this->setType($records_row[1]);
        $this->setRevisionHash($records_row[2]);
        $this->setAddress($records_row[3]);

        return $this;
    }

    /**
     * This method loads the Structure 1 (as a line result of `git ls-files`
     * command).
     *
     * @param string $records_row
     * @param bool $is_db
     *
     * @return $this
     */
    public function loadRowStructure2(string $records_row, bool $is_db)
    {
        $records_row_exploded = explode('/', $records_row);

        $records_row_exploded = end($records_row_exploded);

        if (empty($records_row_exploded)) {
            return $this;
        }

        if ($is_db) {
            $this->setId($this->getIdOfAsset($records_row_exploded));
        }

        $this->setAddress($records_row);

        return $this;
    }

    /**
     * Load structure for the filesystem search
     *
     * @param string $full_database_address (directory tree)
     * @param string $records_row
     *
     * @return $this
     */
    public function loadRowStructureSimpleDir(string $full_database_address, string $records_row)
    {
        if (empty($records_row))
            return $this;

        if ($full_database_address[strlen($full_database_address) - 1] != "/") {
            $full_database_address = $full_database_address . "/";
        }

        $records_address = $full_database_address . $records_row;

        // avoid existent bag records_row to get inside the object attribute "id"
        $records_row_exploded = explode("/", $records_row);
        if (count($records_row_exploded) > 1) {
            $records_row = $records_row_exploded[0];
        }

        $permissions = substr(sprintf('%o', fileperms($records_address)), -4);
        $this->setId($records_row);
        $this->setPermissions($permissions);
        $this->setAddress($records_address);
        $this->setFileTimestamp(filemtime($records_address));
        $this->setFileUpdatedAt(gmdate("Y-m-d H:i:s", $this->getFileTimestamp()));

        return $this;
    }

    /**
     * Return the Id of the physical address
     *
     * @param string $address
     *
     * @return int|string
     */
    public function getIdOfAsset(string $address)
    {
        $address_exploded = explode('/', $address);

        $asset_name = end($address_exploded);

        $id = preg_replace("/[^\d]/", "", $asset_name);

        return $id;
    }

}
