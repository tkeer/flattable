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

    /**
     * @param Model $model
     * @param array $config
     * @return Builders\ManyBuilder|Builders\PrimaryBuilder|Builders\SecondaryBuilder
     * @throws \Exception
     */
    private function getHandler(Model $model, array $config)
    {
        $this->config->set($config);

        $db = new DatabaseManager();

        $changesDataBuilder = new ChangesBuilderHelper($db, $this->config, $model);

        $common = new BuilderHelper($this->config, $changesDataBuilder, $model);

        $factory = new FlattableFactory($db, $this->config, $common, $changesDataBuilder);

        return $factory->create();
    }

    private function callHandlerFunction(Model $model, string $functionName)
    {
        $configs = $model->flattableConfig();

        foreach ($configs as $config) {

            $service = $this->getHandler($model, $config);

            $service->{$functionName}();
        }
    }
}