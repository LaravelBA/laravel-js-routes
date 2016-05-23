<?php

use Illuminate\Filesystem\Filesystem;
use LaravelBA\LaravelJsRoutes\Generators\RoutesJavascriptGenerator;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class RoutesJavascriptGeneratorTest extends MockeryTestCase
{
    /**
     * @var RoutesJavascriptGenerator
     */
    private $gen;

    /**
     * @var Filesystem|\Mockery\Mock
     */
    private $file;

    protected function setUp()
    {
        $router = new Illuminate\Routing\Router(new Illuminate\Events\Dispatcher);

        $router->get('user/{id}', [
            'as'   => 'user.show',
            'uses' => function ($id) {
                return $id;
            },
        ]);
        $router->post('user', [
            'as'         => 'user.store',
            'middleware' => 'js-routable',
            'uses'       => function ($id) {
                return $id;
            },
        ]);
        $router->get('/user/{id}/edit', [
            'as'         => 'user.edit',
            'middleware' => 'js-routable',
            'uses'       => function ($id) {
                return $id;
            },
        ]);
        $router->get('/unnamed_route', [
            'uses' => function ($id) {
                return $id;
            },
        ]);

        $template = file_get_contents(dirname(dirname(__DIR__)) . '/src/Generators/templates/Router.js');

        $this->file = Mockery::mock(Filesystem::class);
        $this->file->shouldReceive('get')->andReturn($template);

        $this->gen = new RoutesJavascriptGenerator($this->file, $router);
    }

    /** @test * */
    public function it_can_generate_javascript()
    {
        $this->file->shouldReceive('isWritable')
            ->once()
            ->andReturn(true);

        $this->file->shouldReceive('put')
            ->once()
            ->with('/foo/bar/routes.js', file_get_contents(__DIR__ . '/stubs/javascript.txt'));

        $this->gen->make('/foo/bar', 'routes.js', ['object' => 'Router']);
    }

    /** @test * */
    public function it_can_generate_javascript_with_custom_object()
    {
        $this->file->shouldReceive('isWritable')
            ->once()
            ->andReturn(true);

        $this->file->shouldReceive('put')
            ->once()
            ->with('/foo/bar/routes.js', file_get_contents(__DIR__ . '/stubs/custom-object.txt'));

        $this->gen->make('/foo/bar', 'routes.js', ['object' => 'MyRouter']);
    }

    /** @test * */
    public function it_can_generate_javascript_with_custom_middleware()
    {
        $this->file->shouldReceive('isWritable')
            ->once()
            ->andReturn(true);

        $this->file->shouldReceive('put')
            ->once()
            ->with('/foo/bar/routes.js', file_get_contents(__DIR__ . '/stubs/custom-filter.txt'));

        $this->gen->make('/foo/bar', 'routes.js', ['middleware' => 'js-routable', 'object' => 'Router']);
    }

    /** @test * */
    public function it_can_generate_javascript_with_custom_prefix()
    {
        $this->file->shouldReceive('isWritable')
            ->once()
            ->andReturn(true);

        $this->file->shouldReceive('put')
            ->once()
            ->with('/foo/bar/routes.js', file_get_contents(__DIR__ . '/stubs/custom-prefix.txt'));

        $this->gen->make('/foo/bar', 'routes.js', ['object' => 'Router', 'prefix' => 'prefix/']);
    }

    /** @test * */
    public function if_fails_on_non_writable_path()
    {
        $this->file->shouldReceive('isWritable')
            ->once()
            ->andReturn(false);

        $this->file->shouldReceive('put')->never();

        $output = $this->gen->make('/foo/bar', 'routes.js', ['filter' => 'js-routable', 'object' => 'Router']);

        $this->assertFalse($output);
    }
}
