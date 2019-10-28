<?php

namespace Firesphere\SolrSearch\Admins;

use Firesphere\SolrSearch\Models\DirtyClass;
use Firesphere\SolrSearch\Models\ElevatedItem;
use Firesphere\SolrSearch\Models\Elevation;
use Firesphere\SolrSearch\Models\SolrLog;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\View\Requirements;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * Class \Firesphere\SolrSearch\Admins\SearchAdmin
 * Manage or see the Solr configuration. Default implementation of SilverStripe ModelAdmin
 * Nothing to see here
 *
 * @package Firesphere\SolrSearch\Admins
 */
class SearchAdmin extends ModelAdmin
{
    /**
     * Models managed by this admin
     *
     * @var array
     */
    private static $managed_models = [
        SolrLog::class,
        DirtyClass::class,
        Elevation::class,
    ];

    /**
     * Add a pretty magnifying glass to the sidebar menu
     *
     * @var string
     */
    private static $menu_icon_class = 'font-icon-search';

    /**
     * Where to find me
     *
     * @var string
     */
    private static $url_segment = 'searchadmin';

    /**
     * My name
     *
     * @var string
     */
    private static $menu_title = 'Search';

    /**
     * Make sure the custom CSS for highlighting in the GridField is loaded
     */
    public function init()
    {
        parent::init();
        Requirements::css('firesphere/solr-search:client/dist/main.css');
        Requirements::javascript('firesphere/solr-search: client/dist/bundle.js');
    }

    public function getEditForm($id = null, $fields = null)
    {
        $oldImportFrom = $this->showImportForm;
        $this->showImportForm = false;
        /** @var GridField $gridField */
        $form = parent::getEditForm($id, $fields);
        $this->showImportForm = $oldImportFrom;

        if ($this->modelClass === ElevatedItem::class) {
            $gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass));

            $gridField
                ->getConfig()
                ->addComponent(new GridFieldOrderableRows('Rank'));
        }

        return $form;
    }
}
