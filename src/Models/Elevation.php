<?php

namespace Firesphere\SolrSearch\Models;

use SilverStripe\ORM\DataObject;
use Firesphere\SolrSearch\Forms\ElevationField;

class Elevation extends DataObject
{
    private static $table_name = 'Elevation';

    private static $db = [
        'Keyword' => 'Varchar(255)',
    ];

    private static $many_many = [
        'Items' => ElevatedItem::class,
    ];

    private static $summary_fields = ['ID', 'Keyword'];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab('Root.Main', ElevationField::create('Keyword', 'Keyword'));
        return $fields;
    }
}
