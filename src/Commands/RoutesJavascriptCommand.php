<?php
namespace LaravelBA\LaravelJsRoutes\Commands;

use Illuminate\Console\Command;
use LaravelBA\LaravelJsRoutes\Generators\RoutesJavascriptGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RoutesJavascriptCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'routes:javascript';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Javascript routes file';

    /**
     * Execute the console command.
     *
     * @param RoutesJavascriptGenerator $generator
     * @return int
     */
    public function handle(RoutesJavascriptGenerator $generator)
    {
        $path = $this->getPath() . '/' . $this->argument('name');
        $middleware = $this->option('middleware');

        if ($this->option('filter')) {
            $this->warn("Filter option is deprecated, as Laravel 5 doesn't use filters anymore." . PHP_EOL .
                        "Please change your code to use --middleware (or -m) instead.");

            $middleware = $this->option('filter');
        }

        $options = [
            'middleware' => $middleware,
            'object'     => $this->option('object'),
            'prefix'     => $this->option('prefix'),
        ];

        if ($generator->make($this->getPath(), $this->argument('name'), $options)) {
            $this->info("Created {$path}");

            return 0;
        }

        $this->error("Could not create {$path}");

        return 1;
    }

    /**
     * Get the path to the file that should be generated.
     *
     * @return string
     */
    protected function getPath()
    {
        return base_path($this->option('path'));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'Filename', 'routes.js'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['path', 'p', InputOption::VALUE_OPTIONAL, 'Path to assets directory.', 'resources/assets/js'],
            ['filter', 'f', InputOption::VALUE_OPTIONAL, 'DEPRECATED: Kept here for compatibility only. Use middleware flag instead.', null],
            ['middleware', 'm', InputOption::VALUE_OPTIONAL, 'Custom route middleware.', null],
            ['object', 'o', InputOption::VALUE_OPTIONAL, 'Custom JS object.', 'Router'],
            ['prefix', 'prefix', InputOption::VALUE_OPTIONAL, 'Custom route prefix.', null],
        ];
    }
}
