<?php

namespace BEdita\Placeholders\Test\TestCase\Event;

use BEdita\API\Controller\FoldersController;
use BEdita\API\Controller\RolesController;
use BEdita\Placeholders\Controller\Component\PlaceholdersComponent;
use BEdita\Placeholders\Event\BootstrapEventHandler;
use BEdita\Placeholders\Model\Behavior\PlaceholdedBehavior;
use BEdita\Placeholders\Model\Behavior\PlaceholdersBehavior;
use Cake\Event\EventManager;
use Cake\Http\ServerRequest;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;

/**
 * {@see \BEdita\Placeholders\Event\BootstrapEventHandler} Test Case
 *
 * @coversDefaultClass \BEdita\Placeholders\Event\BootstrapEventHandler
 */
class BootstrapEventHandlerTest extends TestCase
{
    use LocatorAwareTrait;

    /**
     * @inheritDoc
     */
    public $fixtures = [
        'plugin.BEdita/Core.ObjectTypes',
        'plugin.BEdita/Core.PropertyTypes',
        'plugin.BEdita/Core.Properties',
        'plugin.BEdita/Placeholders.Relations',
        'plugin.BEdita/Placeholders.RelationTypes',
    ];

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        parent::setUp();

        EventManager::instance()->on(new BootstrapEventHandler());
    }

    /**
     * Test {@see BootstrapEventHandler::onControllerInitialize()}.
     *
     * @return void
     *
     * @covers ::onControllerInitialize()
     */
    public function testOnControllerInitialize()
    {
        $controller = new FoldersController(new ServerRequest([
            'environment' => ['HTTP_ACCEPT' => 'application/vnd.api+json'],
            'params' => ['_ext' => 'json', 'object_type' => 'folders'],
        ]));
        $response = $controller->startupProcess();

        static::assertNull($response);
        static::assertTrue($controller->components()->has('Placeholders'));
        static::assertInstanceOf(PlaceholdersComponent::class, $controller->components()->get('Placeholders'));
    }

    /**
     * Test {@see BootstrapEventHandler::onControllerInitialize()} with a generic resources controller.
     *
     * @return void
     *
     * @covers ::onControllerInitialize()
     */
    public function testOnControllerInitializeResourcesController()
    {
        $controller = new RolesController(new ServerRequest([
            'environment' => ['HTTP_ACCEPT' => 'application/vnd.api+json'],
            'params' => ['_ext' => 'json'],
        ]));
        $response = $controller->startupProcess();

        static::assertNull($response);
        static::assertFalse($controller->components()->has('Placeholders'));
    }

    /**
     * Test {@see BootstrapEventHandler::onModelInitialize()}.
     *
     * @return void
     *
     * @covers ::onModelInitialize()
     */
    public function testOnModelInitialize()
    {
        $table = $this->getTableLocator()->get('Documents');

        static::assertTrue($table->hasBehavior('Placeholders'));
        static::assertInstanceOf(PlaceholdersBehavior::class, $table->getBehavior('Placeholders'));
        static::assertTrue($table->hasBehavior('Placeholded'));
        static::assertInstanceOf(PlaceholdedBehavior::class, $table->getBehavior('Placeholded'));
    }

    /**
     * Test {@see BootstrapEventHandler::onModelInitialize()} with a generic table.
     *
     * @return void
     *
     * @covers ::onModelInitialize()
     */
    public function testOnModelInitializeOtherTable()
    {
        $table = $this->getTableLocator()->get('Roles');

        static::assertFalse($table->hasBehavior('Placeholders'));
        static::assertFalse($table->hasBehavior('Placeholded'));
    }
}
