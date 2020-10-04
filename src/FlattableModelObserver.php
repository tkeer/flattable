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
     * @return JsonUpdate|PrimaryUpdate|SecondaryUpdate
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

    private function callHandlerFunction(Model $model, $functionName)
    {
        $configs = $model->flattableConfig();
//        dd($configs);
        foreach ($configs as $config) {
            $service = $this->getHandler($model, $config);

            try {
                $service->{$functionName}();
            } catch (\Exception $e) {
                $this->reportError($model, $e);
            }
        }
    }

    private function setConfigs(array $configs)
    {
        $this->configs = $configs;
    }

    private function reportError($model, $e)
    {
        $event = new UpdateFailed(get_class($model), $e->getMessage());

        \Event::dispatch($event);

        if (\App::environment('local') || \App::environment('testing')) {
            throw $e;
        }
    }
}