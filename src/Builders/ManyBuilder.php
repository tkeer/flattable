<?php

namespace Tkeer\Flattable\Builders;

use Tkeer\Flattable\DatabaseManager;

class ManyBuilder
{
    /**
     * @var DatabaseManager
     */
    protected $db;

    protected $common;

    public function __construct(BuilderHelper $common, DatabaseManager $databaseManager)
    {
        $this->common = $common;
        $this->db = $databaseManager;
    }

    public function create()
    {
        $flatTableJsonColName = $this->common->getFlatTableJsonColName();

        $flatTableName = $this->common->getFlatTableName();

        $dataToFill = $this->common->makeColumnsArray();

        $wheres = $this->common->buildConstraints();

        $this->db->updateJson($flatTableName, $dataToFill, $flatTableJsonColName, $wheres);
    }

    public function update()
    {
        $this->updateFlatTableForUpdatedModel();
    }

    public function delete()
    {
        $flatTableJsonColName = $this->common->getFlatTableJsonColName();

        $flatTableName = $this->common->getFlatTableName();

        $modelId = $this->common->getModelId();

        $wheres = $this->common->buildConstraints();

        $this->db->deleteJson($flatTableName, $modelId, $flatTableJsonColName, $wheres);
    }

    private function updateFlatTableForUpdatedModel()
    {
        $flatTableJsonColName = $this->common->getFlatTableJsonColName();

        $flatTableName = $this->common->getFlatTableName();

        $dataToFill = $this->common->makeColumnsArray();

        $wheres = $this->common->buildConstraints();

        $this->db->updateJson($flatTableName, $dataToFill, $flatTableJsonColName, $wheres);

        //if secondary model now belongs to another primary model, i-e book belongs to another publisher
        if (($wheres = $this->common->buildDeletesFromOldConstraints()) === false) {
            return;
        }
        $modelId = $this->common->getModelId();
        $this->db->deleteJson($flatTableName, $modelId, $flatTableJsonColName, $wheres);
    }
}
