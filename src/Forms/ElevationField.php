<?php

namespace Firesphere\SolrSearch\Forms;

use SilverStripe\Forms\FormField;

class ElevationField extends FormField
{
    private static $default_classes = ['elevation-field'];

    protected $schemaDataType = FormField::SCHEMA_DATA_TYPE_CUSTOM;

    protected $schemaComponent = 'ElevationField';

    /**
     * Attributes to be given for this field type.
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = [
            'class'       => $this->extraClass(),
            'id'          => $this->ID(),
            'name'        => $this->getName(),
            'value'       => $this->Value(),
            'data-schema' => json_encode($this->getSchemaData()),
            'data-state'  => json_encode($this->getSchemaState()),
        ];

        $attributes = array_merge($attributes, $this->attributes);

        $this->extend('updateAttributes', $attributes);

        return $attributes;
    }
}
