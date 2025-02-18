<?php


namespace Firesphere\SolrSearch\Services;

use Exception;
use Firesphere\SolrSearch\Helpers\FieldResolver;
use Firesphere\SolrSearch\Helpers\Statics;
use Firesphere\SolrSearch\Traits\GetSetSchemaServiceTrait;
use SilverStripe\Control\Director;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\ViewableData;

/**
 * Class SchemaService
 *
 * @package Firesphere\SolrSearch\Services
 */
class SchemaService extends ViewableData
{
    use GetSetSchemaServiceTrait;

    /**
     * The field resolver to find a field for a class
     *
     * @var FieldResolver
     */
    protected $fieldResolver;

    /**
     * CoreService to use
     *
     * @var SolrCoreService
     */
    protected $coreService;

    /**
     * SchemaService constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->fieldResolver = Injector::inst()->get(FieldResolver::class);
        $this->coreService = Injector::inst()->get(SolrCoreService::class);
    }

    /**
     * Get all fulltext field definitions that are loaded
     *
     * @return ArrayList
     * @throws Exception
     */
    public function getFulltextFieldDefinitions()
    {
        $return = ArrayList::create();
        $store = $this->store;
        $this->setStore(true);
        foreach ($this->index->getFulltextFields() as $field) {
            $this->getFieldDefinition($field, $return);
        }

        $this->extend('onBeforeFulltextFields', $return);

        $this->setStore($store);

        return $return;
    }

    /**
     * Get the field definition for a single field
     *
     * @param $fieldName
     * @param ArrayList $return
     * @param null|string $copyField
     * @throws Exception
     */
    protected function getFieldDefinition($fieldName, &$return, $copyField = null)
    {
        $field = $this->fieldResolver->resolveField($fieldName);
        $typeMap = Statics::getTypeMap();
        $storeFields = $this->getStoreFields();
        $item = [];
        foreach ($field as $name => $options) {
            // Temporary short-name solution until the Introspection is properly solved
            $name = getShortFieldName($name);
            // Boosted fields are always stored
            $store = ($this->store || array_key_exists($options['name'], $storeFields)) ? 'true' : 'false';
            $item = [
                'Field'       => $name,
                'Type'        => $typeMap[$options['type']],
                'Indexed'     => 'true',
                'Stored'      => $options['store'] ?? $store,
                'MultiValued' => $options['multi_valued'] ? 'true' : 'false',
                'Destination' => $copyField,
            ];
            $return->push($item);
        }

        $this->extend('onAfterFieldDefinition', $return, $item);
    }

    /**
     * Get the stored fields. This includes boosted and faceted fields
     *
     * @return array
     */
    protected function getStoreFields(): array
    {
        $boostedFields = $this->index->getBoostedFields();
        $storedFields = $this->index->getStoredFields();
        $facetFields = $this->index->getFacetFields();
        $facetArray = [];
        foreach ($facetFields as $key => $facetField) {
            $facetArray[] = $key . '.' . $facetField['Field'];
        }
        // Boosts, facets and obviously stored fields need to be stored
        $storeFields = array_merge($storedFields, array_keys($boostedFields), $facetArray);

        return $storeFields;
    }

    /**
     * Get the fields that should be copied
     *
     * @return ArrayList
     */
    public function getCopyFields()
    {
        $fields = $this->index->getCopyFields();

        $return = ArrayList::create();
        foreach ($fields as $field => $copyFields) {
            $item = [
                'Field' => $field,
            ];

            $return->push($item);
        }

        $this->extend('onBeforeCopyFields', $return);

        return $return;
    }

    /**
     * Get the definition of a copy field for determining what to load in to Solr
     *
     * @return ArrayList
     * @throws Exception
     */
    public function getCopyFieldDefinitions()
    {
        $copyFields = $this->index->getCopyFields();

        $return = ArrayList::create();

        foreach ($copyFields as $field => $fields) {
            // Allow all fields to be in a copyfield via a shorthand
            if ($fields[0] === '*') {
                $fields = $this->index->getFulltextFields();
            }

            foreach ($fields as $copyField) {
                $this->getFieldDefinition($copyField, $return, $field);
            }
        }

        return $return;
    }

    /**
     * Get the definitions of a filter field to load in to Solr.
     *
     * @return ArrayList
     * @throws Exception
     */
    public function getFilterFieldDefinitions()
    {
        $return = ArrayList::create();
        $originalStore = $this->store;
        $this->setStore(Director::isDev() ? true : false);
        $fields = $this->index->getFilterFields();
        foreach ($this->index->getFacetFields() as $facetField) {
            $fields[] = $facetField['Field'];
        }
        $fields = array_unique($fields);
        foreach ($fields as $field) {
            $this->getFieldDefinition($field, $return);
        }
        $this->extend('onBeforeFilterFields', $return);

        $this->setStore($originalStore);

        return $return;
    }

    /**
     * Get the types template in a rendered state
     *
     * @return DBHTMLText
     */
    public function getTypes()
    {
        if (!$this->typesTemplate) {
            $solrVersion = $this->coreService->getSolrVersion();
            $dir = ModuleLoader::getModule('firesphere/solr-search')->getPath();
            $template = sprintf('%s/Solr/%s/templates/types.ss', $dir, $solrVersion);
            $this->setTypesTemplate($template);
        }

        return $this->renderWith($this->getTypesTemplate());
    }

    /**
     * Generate the Schema xml
     *
     * @return DBHTMLText
     */
    public function generateSchema()
    {
        if (!$this->template) {
            $solrVersion = $this->coreService->getSolrVersion();
            $dir = ModuleLoader::getModule('firesphere/solr-search')->getPath();
            $template = sprintf('%s/Solr/%s/templates/schema.ss', $dir, $solrVersion);
            $this->setTemplate($template);
        }

        return $this->renderWith($this->getTemplate());
    }

    /**
     * Get any extras that need loading in to Solr
     *
     * @return string
     */
    public function getExtrasPath()
    {
        // @todo configurable but with default to the current absolute path
        $dir = ModuleLoader::getModule('firesphere/solr-search')->getPath();

        $confDirs = SolrCoreService::config()->get('paths');
        $solrVersion = $this->coreService->getSolrVersion();

        return sprintf($confDirs[$solrVersion]['extras'], $dir);
    }
}
