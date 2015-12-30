<?php
namespace IDX\Widgets\Omnibar;

class Advanced_Fields
{
    public function __construct($idxID, $mls_name, $field_names, $property_types)
    {
        $this->idxID = $idxID;
        $this->field_names = $field_names;
        $this->mls_name = $mls_name;
        $this->property_types = $property_types;
    }

    public $idxID;
    public $mls_name;
    public $field_names;
    public $property_types;

    public function return_fields()
    {
        return array(
            'idxID' => $this->idxID,
            'mls_name' => $this->mls_name,
            'field_names' => $this->field_names,
        );
    }

    public function return_mlsPtIDs()
    {
        return array(
            'idxID' => $this->idxID,
            'mls_name' => $this->mls_name,
            'property_types' => $this->property_types,
        );
    }
}
