<?php

namespace Tkeer\Flattable\Builders;

use Tkeer\Flattable\DatabaseManager;

class PrimaryBuilder implements BuilderInterface
{
    /**
     * @var DatabaseManager
     */
    protected $db;

    /**
     * @var BuilderHelper
     */
    protected $helper;

    public function __construct(BuilderHelper $helper, DatabaseManager $databaseManager)
    {
        $this->helper = $helper;
        $this->db = $databaseManager;
    }

    public function create()
    {
        $dataToFill = $this->helper->makeColumnsArray();

        $flatTableName = $this->helper->getFlatTableName();

        $this->db->insert($flatTableName, $dataToFill);
    }

    public function update()
    {
        $flatTableName = $this->helper->getFlatTableName();

        $dataToFill = $this->helper->makeColumnsArray();

        $wheres = $this->helper->buildConstraints();

        $this->db->update($flatTableName, $dataToFill, $wheres);
    }

    public function delete()
    {
        $flatTableName = $this->helper->getFlatTableName();

        $wheres = $this->helper->buildConstraints();

        $this->db->delete($flatTableName, $wheres);
    }
}