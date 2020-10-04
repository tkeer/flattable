<?php

namespace Tkeer\Flattable\Builders;

use Tkeer\Flattable\DatabaseManager;

class SecondaryBuilder
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
        $this->updateFlatTableForUpdatedModel();
    }

    public function update()
    {
        $this->updateFlatTableForUpdatedModel();
    }

    public function delete()
    {
        //if delete secondary model also delete primary
        $this->common->deletesPrimary() ? $this->doDelete() : $this->updateFlatTableForUpdatedModel();
    }

    private function updateFlatTableForUpdatedModel()
    {
        $flatTableName = $this->common->getFlatTableName();

        $dataToFill = $this->common->makeColumnsArray();

        $wheres = $this->common->buildConstraints();

        $this->db->update($flatTableName, $dataToFill, $wheres);
    }

    private function doDelete()
    {
        $flatTableName = $this->common->getFlatTableName();

        $wheres = $this->common->buildConstraints();

        $this->db->delete($flatTableName, $wheres);
    }
}
