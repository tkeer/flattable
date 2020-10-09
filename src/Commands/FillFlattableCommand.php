<?php


namespace Tkeer\Flattable\Commands;


use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Tkeer\Flattable\Builders\BuilderHelper;
use Tkeer\Flattable\Builders\ChangesBuilderHelper;
use Tkeer\Flattable\Builders\PrimaryBuilder;
use Tkeer\Flattable\ConfigurationManager;
use Tkeer\Flattable\DatabaseManager;
use Tkeer\Flattable\FlattableFactory;

class FillFlattableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flattable:fill {model} {chunk=500}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fills flattable.';

    public function handle(ConfigurationManager $configManager)
    {
        $modelName = $this->argument('model');
        $model = new $modelName;

        if (! method_exists($model, 'flattableConfig')) {
            $this->error('"flattableConfig" method is missing in your model:' . $modelName);
            return;
        }

        $primaryConfig = $this->filterPrimaryConfig($model->flattableConfig());

        if (! $primaryConfig) {
            $this->error($modelName . ' is missing "primary" config');
            return;
        }

        $configManager->set($primaryConfig);

        $model::chunk($this->argument('chunk'), function ($models) use ($configManager) {
            $models->each(function ($model) use ($configManager) {
                $handler = $this->getPrimaryBuilder($model, $configManager);
                $handler->create();
            });
        });
    }


    private function getPrimaryBuilder(Model $model, ConfigurationManager $config)
    {
        $db = new DatabaseManager();

        $changesDataBuilder = new ChangesBuilderHelper($db, $config, $model);
        $changesDataBuilder->disableDirtyCheck();

        $common = new BuilderHelper($config, $changesDataBuilder, $model);

        $factory = new FlattableFactory($db, $config, $common, $changesDataBuilder);

        return $factory->create();

    }

    private function filterPrimaryConfig(array $configs)
    {
        return Arr::first($configs, function ($config) {
            return ($config['type'] ?? '') === 'primary';
        });
    }
}