<?php
namespace LaravelBA\LaravelJsRoutes\Commands;

use Illuminate\Contracts\Container\Container;
use LaravelBA\LaravelJsRoutes\Generators\RoutesJavascriptGenerator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class RoutesJavascriptCommandTest extends MockeryTestCase
{
    /**
     * @var RoutesJavascriptCommand
     */
    private $command;

    /**
     * @var \Mockery\Mock|RoutesJavascriptGenerator
     */
    private $generator;

    protected function setUp()
    {
        /** @var Container|\Mockery\Mock $app */
        $app = Mockery::mock(Container::class);

        $app->shouldReceive('make')
            ->with('path.base')
            ->andReturn('/foo/bar');

        $app->shouldReceive('call')->andReturnUsing(function () {
            return $this->command->handle($this->generator);
        });

        $this->generator = Mockery::mock(RoutesJavascriptGenerator::class);
        $this->command   = new RoutesJavascriptCommand();
        $this->command->setLaravel($app);
    }

    /** @test * */
    public function it_generated_javascript()
    {
        $this->generator->shouldReceive('make')->once()
            ->with('/foo/bar', 'routes.js', ['middleware' => null, 'object' => 'Router', 'prefix' => null])
            ->andReturn(true);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $this->assertEquals("Created /foo/bar/routes.js\n", $tester->getDisplay());
    }

    /** @test * */
    public function it_can_set_custom_path_and_custom_object_and_prefix()
    {
        $this->generator->shouldReceive('make')->once()
            ->with('assets/js', 'myRoutes.js', ['middleware' => null, 'object' => 'MyRouter', 'prefix' => 'prefix/'])
            ->andReturn(true);

        $tester = new CommandTester($this->command);
        $tester->execute([
            'name'     => 'myRoutes.js',
            '--path'   => 'assets/js',
            '--object' => 'MyRouter',
            '--prefix' => 'prefix/',
        ]);

        $this->assertEquals("Created assets/js/myRoutes.js\n", $tester->getDisplay());
    }

    /** @test * */
    public function it_fails_on_unexistent_path()
    {
        $this->generator->shouldReceive('make')
            ->once()
            ->with('unexistent/path', 'myRoutes.js', ['middleware' => null, 'object' => 'Router', 'prefix' => null])
            ->andReturn(false);

        $tester = new CommandTester($this->command);
        $tester->execute(['name' => 'myRoutes.js', '--path' => 'unexistent/path']);

        $this->assertEquals("Could not create unexistent/path/myRoutes.js\n", $tester->getDisplay());
    }
}

/**
 * Monkey patching Laravel's `base_path()` function.
 *
 * @return string
 */
function base_path()
{
    return '/foo/bar';
}
