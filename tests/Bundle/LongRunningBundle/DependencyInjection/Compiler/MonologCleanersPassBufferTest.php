<?php

namespace LongRunning\Tests\Bundle\LongRunningBundle\DependencyInjection\Compiler;

use LongRunning\Bundle\LongRunningBundle\DependencyInjection\Compiler\MonologCleanersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class MonologCleanersPassBufferTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var Definition
     */
    private $closeBufferHandlersDefinition;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();

        $this->closeBufferHandlersDefinition = new Definition('LongRunning\Plugin\MonologPlugin\CloseBufferHandlers', array(array()));
        $this->container->setDefinition('long_running.monolog.close_buffer_handlers', $this->closeBufferHandlersDefinition);

        $this->container->addCompilerPass(new MonologCleanersPass());
    }

    /**
     * @test
     */
    public function it_gets_close_buffer_handlers()
    {
        $this->createBufferHandler('buffer.handler1');
        $this->createBufferHandler('buffer.handler2');
        $this->createBufferHandler('buffer.handler3');

        $this->container->compile();

        $this->resolverContainsBufferHandler(array('buffer.handler1', 'buffer.handler2', 'buffer.handler3'));
    }

    private function createBufferHandler($name)
    {
        $handler = new Definition('Monolog\Handler\BufferHandler', array(array()));
        $this->container->setDefinition($name, $handler);
    }

    private function resolverContainsBufferHandler($expectedResolverIds)
    {
        $actualResolverIds = [];
        foreach ($this->closeBufferHandlersDefinition->getArgument(0) as $argument) {
            $this->assertInstanceOf(
                'Symfony\Component\DependencyInjection\Reference',
                $argument
            );
            $actualResolverIds[] = (string) $argument;
        }
        $this->assertEquals($expectedResolverIds, $actualResolverIds);
    }
}
