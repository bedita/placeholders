<?php
declare(strict_types=1);

namespace BEdita\Placeholders\Test\TestCase\Event;

use BEdita\Placeholders\Event\BootstrapEventHandler;
use BEdita\Placeholders\Event\JsonSchemaEventHandler;
use Cake\Datasource\ModelAwareTrait;
use Cake\Event\EventManager;
use Cake\TestSuite\TestCase;

/**
 * {@see \BEdita\Placeholders\Event\JsonSchemaEventHandler} Test Case
 *
 * @coversDefaultClass \BEdita\Placeholders\Event\JsonSchemaEventHandler
 * @property \BEdita\Core\Model\Table\ObjectTypesTable $ObjectTypes
 */
class JsonSchemaEventHandlerTest extends TestCase
{
    use ModelAwareTrait;

    /**
     * @inheritDoc
     */
    public $fixtures = [
        'plugin.BEdita/Core.ObjectTypes',
        'plugin.BEdita/Core.PropertyTypes',
        'plugin.BEdita/Core.Properties',
        'plugin.BEdita/Placeholders.Relations',
        'plugin.BEdita/Placeholders.RelationTypes',
        'plugin.BEdita/Core.Objects',
        'plugin.BEdita/Placeholders.ObjectRelations',
        'plugin.BEdita/Core.Locations',
        'plugin.BEdita/Core.Media',
        'plugin.BEdita/Core.Profiles',
        'plugin.BEdita/Core.Users',
        'plugin.BEdita/Core.Streams',
        'plugin.BEdita/Core.Trees',
        'plugin.BEdita/Core.Categories',
        'plugin.BEdita/Core.ObjectCategories',
        'plugin.BEdita/Core.History',
    ];

    /**
     * @inheritDoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->loadModel('ObjectTypes');

        EventManager::instance()->on(new JsonSchemaEventHandler());
    }

    public function tearDown()
    {
        $this->getTableLocator()->clear();

        parent::tearDown();
    }

    /**
     * Test {@see JsonSchemaEventHandler::onGetSchema()}.
     *
     * @return void
     * @covers ::onGetSchema()
     */
    public function testOnGetSchema(): void
    {
        EventManager::instance()->on(new BootstrapEventHandler());
        $schema = $this->ObjectTypes->get('documents')->schema;

        // Check that relevant fields have `placeholders` set to `true`.
        static::assertArrayHasKey('description', $schema['properties']);
        static::assertArrayHasKey('body', $schema['properties']);
        static::assertGreaterThan(2, count($schema['properties']));
        foreach ($schema['properties'] as $field => $subSchema) {
            switch ($field) {
                case 'description':
                case 'body':
                    static::assertArrayHasKey('placeholders', $subSchema);
                    static::assertSame(true, $subSchema['placeholders']);
                    break;

                default:
                    static::assertArrayNotHasKey('placeholders', $subSchema);
            }
        }

        // Check that relevant relations have `readonly` set to `true`.
        static::assertArrayHasKey('placeholder', $schema['relations']);
        static::assertArrayHasKey('placeholded', $schema['relations']);
        static::assertGreaterThan(2, count($schema['relations']));
        foreach ($schema['relations'] as $field => $subSchema) {
            switch ($field) {
                case 'placeholder':
                case 'placeholded':
                    static::assertArrayHasKey('readonly', $subSchema);
                    static::assertSame(true, $subSchema['readonly']);
                    break;

                default:
                    static::assertArrayNotHasKey('readonly', $subSchema);
            }
        }
    }

    /**
     * Test {@see JsonSchemaEventHandler::onGetSchema()}.
     *
     * @return void
     * @covers ::onGetSchema()
     */
    public function testOnGetSchemaWithoutPlaceholders(): void
    {
        $schema = $this->ObjectTypes->get('documents')->schema;

        static::assertArrayHasKey('description', $schema['properties']);
        static::assertArrayHasKey('body', $schema['properties']);
        static::assertGreaterThan(2, count($schema['properties']));
        foreach ($schema['properties'] as $subSchema) {
            static::assertArrayNotHasKey('placeholders', $subSchema);
        }

        static::assertArrayHasKey('placeholder', $schema['relations']);
        static::assertArrayHasKey('placeholded', $schema['relations']);
        static::assertGreaterThan(2, count($schema['relations']));
        foreach ($schema['relations'] as $subSchema) {
            static::assertArrayNotHasKey('readonly', $subSchema);
        }
    }
}
