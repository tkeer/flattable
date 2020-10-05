<?php

namespace Tkeer\Flattable\Builders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Tkeer\Flattable\ConfigurationManager;
use Tkeer\Flattable\DatabaseManager;

class BuilderHelper
{
    /**
     * @var Model
     */
    protected $model;

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

    /**
     * @var ChangesBuilderHelper
     */
    private $changesBuilder;

    public function __construct(
        ConfigurationManager $configurationManager,
        ChangesBuilderHelper $changeBuilder,
        Model $model
    ) {
        $this->config = $configurationManager;

        $this->setModel($model);

        //default callback
        $this->constraintCallback = function () {};
        $this->changesBuilder = $changeBuilder;
    }

    /**
     * in case of many relation, we store json array in the flattable
     * get the name of the column where this json array is stored
     *
     * @return string
     */
    public function getFlatTableJsonColName()
    {
        return $this->config->getFlatTableJsonColName();
    }

    /**
     * @return string
     */
    public function getFlatTableName()
    {
        return $this->config->getFlatTableName();
    }

    /**
     * prepare data to be save in the flattable
     *
     * @return array
     */
    public function makeColumnsArray()
    {
        $columnsConfig = $this->config->getColumnsConfig();

        if (is_callable($columnsConfig)) {
            $data = call_user_func_array($columnsConfig, [$this->getModel()]);
            return $data;
        }

        //cols to be updated
        $flatTableColumns = array_keys($columnsConfig);

        $data = $this->getDataFromModel();
        $data = array_combine($flatTableColumns, $data);

        //get data for flat table when related model ids changes
        $changesData = $this->changesBuilder->getChangesData();

        $data = array_merge($data, $changesData);

        return $data;
    }

    /**
     * use 'columns' config, and make data from the source model
     *
     * @return array
     */
    public function getDataFromModel()
    {
        $updateModelColumnsNames = $this->getSourceModelColumnNames();

        //map update model column' names to respective data
        $data = array_map(function ($columnName) {
            return $this->getModelValueByColumnName($columnName);
        }, $updateModelColumnsNames);

        return $data;
    }

    private function getModelValueByColumnName($columnName)
    {
        $model = $this->getModel();

        $value = data_get($model, $columnName);

        if ($this->shouldIncludeDeletedModel()) {
            return $value;
        }

        //send empty array if model is deleted
        return $this->isModelDeleted() ? null : $value;
    }

    /**
     * get the attribute names of the source model
     *
     * @return array|mixed
     */
    private function getSourceModelColumnNames()
    {
        $columnsConfig = $this->config->getColumnsConfig();

        return array_values($columnsConfig);
    }

    /**
     * map where constraints given in the config to the constraints to be used to fetch/update flattable in db
     *
     * @param bool $fromOriginalValues if model values to get from original values
     * @return array|\Closure
     */
    public function buildConstraints($fromOriginalValues = false)
    {
        $constraintConfigs = $this->config->getConstraintsConfig();

        if (is_callable($constraintConfigs)) {
            //save this callback, db mangager will call our callback with query
            //we will call this callback with query and model instance
            $this->constraintCallback = $constraintConfigs;

            return $this->queryCallback();
        }

        return collect($constraintConfigs)->map(function ($constraintConfig) use ($fromOriginalValues) {
            return $this->buildConstraint($constraintConfig, $fromOriginalValues);
        });
    }

    /**
     * same as above, for single constraint
     *
     * @param array $constraintConfig
     * @param bool $fromOriginalValues
     * @return array
     */
    private function buildConstraint(array $constraintConfig, $fromOriginalValues): array
    {
        $columnName = Arr::get($constraintConfig, 'flattable_column_name');
        $op = Arr::get($constraintConfig, 'op', '=');
        $sourceModelColumnName = Arr::get($constraintConfig, 'column_name');
        $useOld = $this->shouldUseModelOldValue($constraintConfig); //whether to use old original model value
        $value = $this->getAttributeValueOfSourceModel($sourceModelColumnName, $fromOriginalValues || $useOld);

        return [
            'column_name' => $columnName,
            'op' => $op,
            'value' => $value,
        ];

    }

    public function queryCallback()
    {
        return function ($query) {
            call_user_func_array($this->constraintCallback, [$query, $this->getModel()]);
        };
    }

    private function getAttributeValueOfSourceModel($columnName, $fromOriginalValues)
    {
        $updateModel = $this->getModel();

        if ($fromOriginalValues) {
            return $updateModel->getOriginal($columnName);
        }

        return data_get($updateModel, $columnName);
    }

    /**
     * check if model is deleted/soft-deleted
     *
     * @return bool
     */
    private function isModelDeleted()
    {
        $model = $this->getModel();

        return is_deleted($model);
    }

    /**
     * do we still need to include attribute's value of the model when model itself is deleted
     *
     * @return bool
     */
    private function shouldIncludeDeletedModel()
    {
        return $this->config->isMany();
    }

    /**
     * @return mixed
     */
    public function getModelId()
    {
        $model = $this->getModel();

        return data_get($model, 'id');
    }

    /**
     * @param Model $model
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * in secondary type relation, if source model is deleted, should the flattable entry also be deleted
     *
     * @return mixed
     */
    public function deletesPrimary()
    {
        return $this->config->deletesPrimary();
    }

    /**
     * when a related key is updated in the modal, and it's entry should be removed from the json column
     *
     * build where constraints to fetch related entry for old key
     *
     * @return array|bool|\Closure
     */
    public function buildDeletesFromOldConstraints()
    {
        if (!$this->shouldDeleteFromOld()) {
            return false;
        }

        return $this->buildConstraints($fromOriginalValues = true);
    }

    /**
     * In case of 'many' relation, if related key is updated, then should the data be removed from old flattable row
     *
     * ie a book belongs to a publisher 1, then the publisher is updated for the book and now publisher is 2
     * then the current book entry from the old publisher json column should be removed
     *
     * @return bool
     */
    public function shouldDeleteFromOld(): bool
    {
        $model = $this->getModel();

        $deleteFromOldKeys = $this->config->getDeletesFromOldKeys();

        $hasDirtyKey = collect($deleteFromOldKeys)
            ->first(function ($key) use ($model) {
                return $model->getAttribute($key) != $model->getOriginal($key);
            });

        return !! $hasDirtyKey;
    }

    /**
     * weather to use old attributes or newly updated attributes of the model
     *
     * @param array $constraintConfig
     * @return bool
     */
    private function shouldUseModelOldValue(array $constraintConfig): bool
    {
        $model = $this->getModel();

        //dont use old values for newly created models
        //it will always return null
        if ($model->wasRecentlyCreated) {
            return false;
        }

        return Arr::get($constraintConfig, 'use_old', true);
    }
}
