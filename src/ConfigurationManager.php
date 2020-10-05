<?php


namespace Tkeer\Flattable;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;

class ConfigurationManager
{
    /**
     * Meta for updating flat tables.
     *
     * @var array
     */
    private $config = [];

    public function set(array $config)
    {
        $this->config = $config;
    }

    public function get()
    {
        return $this->config;
    }

    public function type()
    {
        return Arr::get($this->get(), 'type');
    }

    public function getColumnsConfig()
    {
        return Arr::get($this->get(), 'columns');
    }

    public function getConstraintsConfig()
    {
        return Arr::get($this->get(), 'wheres');
    }

    public function getFlatTableName()
    {
        $configs = $this->get();

        return Arr::get($configs, 'flattable');
    }

    public function getFlatTableJsonColName()
    {
        $configs = $this->get();

        return Arr::get($configs, 'flattable_column_name');
    }

    public function deletesPrimary()
    {
        return Arr::get($this->get(), 'deletes_primary');
    }

    public function isMany()
    {
        $configs = $this->get();

        return Arr::get($configs, 'type') === 'many';
    }

    public function getDeletesFromOldKeys()
    {
        $configs = $this->get();

        return Arr::get($configs, 'delete_from_old_keys', []);
    }
}
