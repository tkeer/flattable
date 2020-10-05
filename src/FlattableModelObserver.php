<?php

namespace Tkeer\Flattable;

use Illuminate\Database\Eloquent\Model;
use Tkeer\Flattable\Builders\BuilderHelper;
use Tkeer\Flattable\Builders\ChangesBuilderHelper;
use Tkeer\Flattable\Events\UpdateFailed;

class FlattableModelObserver
{
    /**
     * @var ConfigurationManager
     */
    private $config;
    /**
     * @var DatabaseManager
     */
    private $db;

    public function __construct(DatabaseManager $databaseManager, ConfigurationManager $configurationManager)
    {
        $this->db = $databaseManager;
        $this->config = $configurationManager;
    }

    public function created(Model $model)
    {
        $this->callHandlerFunction($model, 'create');
    }

    public function updated(Model $model)
    {
        $this->callHandlerFunction($model, 'update');
    }

    public function deleted(Model $model)
    {
        $this->callHandlerFunction($model, 'delete');
    }

    private function callHandlerFunction(Model $model, string $functionName)
    {
        if ($model::isFlattableDisabled()) {
            return;
        }

        $configs = $model->flattableConfig();

        foreach ($configs as $config) {

            $service = $this->getHandler($model, $config);

            $service->{$functionName}();
        }
    }

    /**
     * @param Model $model
     * @param array $config
     * @return Builders\ManyBuilder|Builders\PrimaryBuilder|Builders\SecondaryBuilder|Builders\VoidBuilder
     * @throws \Exception
     */
    private function getHandler(Model $model, array $config)
    {
        $this->config->set($config);

        $db = new DatabaseManager();

        $changesDataBuilder = new ChangesBuilderHelper($db, $this->config, $model);

        $common = new BuilderHelper($this->config, $changesDataBuilder, $model);

        $factory = new FlattableFactory($db, $this->config, $common, $changesDataBuilder);

        // only process if any attribute of the given is dirty
        if ($common->hasDirtyAttribute()) {
            return $factory->create();
        }

        return $factory->getVoidBuilder();
    }
}