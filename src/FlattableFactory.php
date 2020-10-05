<?php

namespace Tkeer\Flattable;

use Illuminate\Database\Eloquent\Model;
use Tkeer\Flattable\Builders\BuilderHelper;
use Tkeer\Flattable\Builders\BuilderInterface;
use Tkeer\Flattable\Builders\ChangesBuilderHelper;
use Tkeer\Flattable\Builders\ManyBuilder;
use Tkeer\Flattable\Builders\PrimaryBuilder;
use Tkeer\Flattable\Builders\SecondaryBuilder;
use Tkeer\Flattable\Builders\VoidBuilder;

class FlattableFactory
{
    const TYPE_PRIMARY = 'primary';
    const TYPE_SECONDARY = 'secondary';
    const TYPE_MANY = 'many';
    const TYPE_VOID = 'void';

    /**
     * @var string
     */
    private $type;
    /**
     * @var Model
     */
    private $model;
    /**
     * @var ChangesBuilderHelper
     */
    private $changesDataBuilder;
    /**
     * @var ConfigurationManager
     */
    private $config;
    /**
     * @var BuilderHelper
     */
    private $common;
    /**
     * @var DatabaseManager
     */
    private $db;

    public function __construct(
        DatabaseManager $databaseManager,
        ConfigurationManager $configurationManager,
        BuilderHelper $common,
        ChangesBuilderHelper $changesDataBuilder
    ) {
        $this->common = $common;
        $this->config = $configurationManager;
        $this->db = $databaseManager;
        $this->changesDataBuilder = $changesDataBuilder;
    }

    public function create()
    {
        switch ($this->config->type()) {
            case self::TYPE_PRIMARY:
                return new PrimaryBuilder($this->common, $this->db);

            case self::TYPE_MANY:
                return new ManyBuilder($this->common, $this->db);

            case self::TYPE_SECONDARY:
                return new SecondaryBuilder($this->common, $this->db);

            default:
                throw new \Exception('Type is missing in the configuration');
        }
    }
}
