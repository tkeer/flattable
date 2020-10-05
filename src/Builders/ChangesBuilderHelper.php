<?php

namespace Tkeer\Flattable\Builders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Tkeer\Flattable\ConfigurationManager;
use Tkeer\Flattable\DatabaseManager;

class ChangesBuilderHelper
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var DatabaseManager
     */
    protected $db;

    /**
     * @var ConfigurationManager
     */
    protected $config;

    /**
     * DB query callback.
     *
     * @var callable
     */
    protected $constraintCallback;

    public function __construct(
        DatabaseManager $databaseManager,
        ConfigurationManager $configurationManager,
        Model $model
    ) {
        $this->db = $databaseManager;
        $this->config = $configurationManager;

        $this->setModel($model);

        //default callback
        $this->constraintCallback = function () {};
    }

    /**
     * source data of the flattable can come from indirect table
     * ie publisher ==> book => reading activity
     *
     * use the changes config, and if relationship column given in the changes config is updated
     * then load data from the table given in the changes config
     *
     * @return array
     */
    public function getChangesData()
    {
        $updatedColumnsConfig = $this->getUpdatedColumnsConfigForChangesData();

        $changeData = $this->buildDataForChangesColumns($updatedColumnsConfig);

        return $changeData;
    }

    /**
     * get changes configuration of dirty columns only
     *
     * @return array
     */
    private function getUpdatedColumnsConfigForChangesData()
    {
        $updatedColumnsConfig = Arr::get($this->config->get(), 'changes', []);

        $updatedColumns = $this->getModel()->getDirty();

        //assuming, we'll be managing only foreign key, trim out null changes
        $updatedColumns = array_filter($updatedColumns, function ($updatedColumn) {
            return (bool)$updatedColumn;
        });

        $updatedColumnNames = array_keys($updatedColumns);

        $columnsToLookForChange = array_keys($updatedColumnsConfig);

        //look for only those cols which are updated
        $changesCols = array_intersect($updatedColumnNames, $columnsToLookForChange);

        //config for cols which actually need to be updated
        $updatedColumnsConfig = Arr::only($updatedColumnsConfig, $changesCols);

        return $updatedColumnsConfig;
    }

    /**
     * use the changes configuration, and build data to be stored in flattable
     *
     * @param $updatedColumnsConfig
     * @return array
     */
    private function buildDataForChangesColumns(array $updatedColumnsConfig)
    {
        $changesAllData = [];

        $model = $this->getModel();

        foreach ($updatedColumnsConfig as $columnName => $columnConfig) {
            $data = $this->buildDataForChangesColumn($columnConfig, $columnName, $model);
            $changesAllData = array_merge($changesAllData, $data);
        }

        return $changesAllData;
    }

    /**
     * build data for single columns config.
     *
     * @param $changeConfig
     * @param $tableColumnName string flat table column name
     * @param $model
     * @return array
     */
    private function buildDataForChangesColumn($changeConfig, $tableColumnName, $model)
    {
        $columnsConfig = Arr::get($changeConfig, 'columns');

        $selects = array_values($columnsConfig);

        $changesTableData = $this->getDataFromTableForChangesColumn($changeConfig, $tableColumnName, $model, $selects);

        $columnsConfig = Arr::get($changeConfig, 'columns');
        $flatTableColumnsThatChanges = array_keys($columnsConfig);

        //remove column names
        $values = array_values($changesTableData);

        //combine flat table columns and changes table data
        $changesData = array_combine($flatTableColumnsThatChanges, $values);

        $insideChangeConfig = Arr::get($changeConfig, 'changes', []);

        $insideChangesData = $this->buildDataForInsideChangesColumn($insideChangeConfig, $changesTableData);

        $changesData = array_merge($changesData, $insideChangesData);

        return $changesData;
    }

    /**
     * if changes are given inside the changes config
     * then use the configuration and load data from db
     *
     * @param array $configs
     * @param $model
     * @return array
     */
    private function buildDataForInsideChangesColumn(array $configs, $model)
    {
        $flatTableData = [];

        foreach ($configs as $columnName => $columnConfig) {
            $columnsConfig = Arr::get($columnConfig, 'columns');

            $selects = array_values($columnsConfig);

            $modelColumnName = Arr::get($columnConfig, 'model_column_name', 'id');

            $changesTableData = $this->getDataFromTableForChangesColumn($columnConfig, $modelColumnName, $model,
                $selects);

            $flatTableColumnsThatChanges = array_keys($columnsConfig);

            //remove column names, get only values
            $values = array_values($changesTableData);

            //if data returned for changes data is null, fill selects column with null values
            if (! $values) {
                $values = array_fill(0, count($flatTableColumnsThatChanges), null);
            }

            //combine flat table columns and changes table data
            $changesData = array_combine($flatTableColumnsThatChanges, $values);

            $insideChangeConfig = Arr::get($columnConfig, 'changes', []);

            if ($values && $insideChangeConfig) {
                $insideChangesData = $this->buildDataForInsideChangesColumn($insideChangeConfig, $changesTableData);

                $changesData = array_merge($changesData, $insideChangesData);
            }

            $flatTableData = array_merge($flatTableData, $changesData);
        }

        return $flatTableData;
    }

    /**
     * load data from table for change config
     *
     * @param $changeConfig
     * @param $tableColumnName
     * @param $model
     * @param $selects
     * @return array
     */
    private function getDataFromTableForChangesColumn($changeConfig, $tableColumnName, $model, $selects)
    {
        $tableName = Arr::get($changeConfig, 'table');

        $wheres[] = [
            'column_name' => Arr::get($changeConfig, 'column_name', 'id'),
            'op' => '=',
            'value' => data_get($model, $tableColumnName),
        ];

        return $this->db->first($tableName, $selects, $wheres);
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }

}
