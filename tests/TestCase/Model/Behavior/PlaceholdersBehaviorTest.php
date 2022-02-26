<?php
/**
 * BEdita, API-first content management framework
 * Copyright 2022 Atlas Srl, Chialab Srl
 *
 * This file is part of BEdita: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See LICENSE.LGPL or <http://gnu.org/licenses/lgpl-3.0.html> for more details.
 */

namespace BEdita\Placeholders\Test\TestCase\Model\Behavior;

use BEdita\Core\Model\Action\AddRelatedObjectsAction;
use BEdita\Placeholders\Event\BootstrapEventHandler;
use BEdita\Placeholders\Model\Behavior\PlaceholdersBehavior;
use Cake\Datasource\ModelAwareTrait;
use Cake\Event\EventManager;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;

/**
 * {@see \BEdita\Placeholders\Model\Behavior\PlaceholdersBehavior} Test Case
 *
 * @coversDefaultClass \BEdita\Placeholders\Model\Behavior\PlaceholdersBehavior
 *
 * @property \BEdita\Core\Model\Table\ObjectsTable $Documents
 * @property \BEdita\Core\Model\Table\MediaTable $Media
 */
class PlaceholdersBehaviorTest extends TestCase
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

        EventManager::instance()->on(new BootstrapEventHandler());

        $this->loadModel('Documents');
        $this->loadModel('Media');
    }

    /**
     * Data provider for {@see PlaceholdersBehaviorTest::testExtractPlaceholders()} test case.
     *
     * @return array[]
     */
    public function extractPlaceholdersProvider(): array
    {
        return [
            'no configured fields' => [
                [],
                [
                    'body' => '<p>This is a text</p><!-- BE-PLACEHOLDER.10.eyAiZm9vIjogImJhciIgfQ== -->',
                ],
                [],
            ],
            'missing field' => [
                [],
                [
                    'body' => '<p>This is a text</p><!-- BE-PLACEHOLDER.10.eyAiZm9vIjogImJhciIgfQ== -->',
                ],
                ['description'],
            ],
            'simple' => [
                [
                    [
                        'id' => 10,
                        'params' => [
                            'body' => [
                                [
                                    'offset' => 21,
                                    'length' => 26,
                                    'params' => null,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'body' => '<p>This is a text</p><!-- BE-PLACEHOLDER.10 -->',
                ],
            ],
            'simple, extra spaces' => [
                [
                    [
                        'id' => 10,
                        'params' => [
                            'body' => [
                                [
                                    'offset' => 21,
                                    'length' => 30,
                                    'params' => null,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'body' => '<p>This is a text</p><!--  BE-PLACEHOLDER.10    -->',
                ],
            ],
            'simple, no spaces' => [
                [
                    [
                        'id' => 10,
                        'params' => [
                            'body' => [
                                [
                                    'offset' => 21,
                                    'length' => 24,
                                    'params' => null,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'body' => '<p>This is a text</p><!--BE-PLACEHOLDER.10-->',
                ],
            ],
            'simple, with params' => [
                [
                    [
                        'id' => 10,
                        'params' => [
                            'body' => [
                                [
                                    'offset' => 21,
                                    'length' => 51,
                                    'params' => '{ "foo": "bar" }',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'body' => '<p>This is a text</p><!-- BE-PLACEHOLDER.10.eyAiZm9vIjogImJhciIgfQ== -->',
                ],
            ],
            'multibyte string, multiple fields, with params' => [
                [
                    [
                        'id' => 10,
                        'params' => [
                            'description' => [
                                [
                                    'offset' => 0,
                                    'length' => 26,
                                    'params' => null,
                                ],
                                [
                                    'offset' => 55,
                                    'length' => 51,
                                    'params' => '{ "foo": "bar" }',
                                ],
                            ],
                            'body' => [
                                [
                                    'offset' => 25,
                                    'length' => 26,
                                    'params' => null,
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 14,
                        'params' => [
                            'body' => [
                                [
                                    'offset' => 75,
                                    'length' => 43,
                                    'params' => 'not a json',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => '<strong>This won\'t be extracted: <!-- BE-PLACEHOLDER.1 --></strong>',
                    'description' => '<!-- BE-PLACEHOLDER.10 --><h1>My sweet placeholder</h1><!-- BE-PLACEHOLDER.10.eyAiZm9vIjogImJhciIgfQ== -->',
                    'body' => '<p>Is this working? 😶</p><!-- BE-PLACEHOLDER.10 --><p>Apparently yes! 👍</p><!-- BE-PLACEHOLDER.14.bm90IGEganNvbg== -->',
                ],
            ],
        ];
    }

    /**
     * Test {@see PlaceholdersBehavior::extractPlaceholders()}.
     *
     * @param array[] $expected Expected result.
     * @param array $data Entity data.
     * @param string[] $fields Fields.
     * @return void
     *
     * @dataProvider extractPlaceholdersProvider()
     * @covers ::extractPlaceholders()
     */
    public function testExtractPlaceholders(array $expected, array $data, array $fields = ['description', 'body']): void
    {
        $entity = new Entity($data);
        $actual = PlaceholdersBehavior::extractPlaceholders($entity, $fields);

        static::assertSame($expected, $actual);
    }

    /**
     * Test {@see PlaceholdersBehavior::afterSave()}.
     *
     * @return void
     *
     * @covers ::afterSave()
     * @covers ::getAssociation()
     * @covers ::prepareEntities()
     */
    public function testSavePlaceholders(): void
    {
        $body = '<!-- BE-PLACEHOLDER.10 --><h1>My sweet placeholder</h1><!-- BE-PLACEHOLDER.10.eyAiZm9vIjogImJhciIgfQ== -->';
        $expected = [
            10 => [
                'body' => [
                    [
                        'offset' => 0,
                        'length' => 26,
                        'params' => null,
                    ],
                    [
                        'offset' => 55,
                        'length' => 51,
                        'params' => '{ "foo": "bar" }',
                    ],
                ],
            ],
        ];

        // Save with placeholder in body.
        $document = $this->Documents->get(2, ['contain' => ['ObjectTypes']]);
        $document->body = $body;
        $this->Documents->saveOrFail($document);

        // Reload entity from database, with placeholders.
        $document = $this->Documents->get(2, ['contain' => ['ObjectTypes', 'Placeholder']]);
        static::assertSame($body, $document->body, 'Entity body has been changed');
        static::assertTrue($document->has('placeholder'));

        // Run assertions.
        $placeholders = $document->get('placeholder');
        $ids = Hash::extract($placeholders, '{n}.id');
        static::assertSame(array_keys($expected), $ids);
        foreach ($placeholders as $placeholder) {
            $id = Hash::get($placeholder, 'id');
            $params = Hash::get($placeholder, '_joinData.params');

            static::assertSame($expected[$id], $params);
        }
    }

    /**
     * Test {@see PlaceholdersBehavior::afterSave()}.
     *
     * @return void
     *
     * @covers ::afterSave()
     * @covers ::getAssociation()
     * @covers ::prepareEntities()
     */
    public function testSavePlaceholdersReplace(): void
    {
        $body = '<!-- BE-PLACEHOLDER.10 --><h1>My sweet placeholder</h1><!-- BE-PLACEHOLDER.10.eyAiZm9vIjogImJhciIgfQ== -->';
        $expected = [
            10 => [
                'body' => [
                    [
                        'offset' => 0,
                        'length' => 26,
                        'params' => null,
                    ],
                    [
                        'offset' => 55,
                        'length' => 51,
                        'params' => '{ "foo": "bar" }',
                    ],
                ],
            ],
        ];

        // Save hypothetical previous data.
        $action = new AddRelatedObjectsAction(['association' => $this->Documents->getAssociation('Placeholder')]);
        $action([
            'entity' => $this->Documents->get(2, ['contain' => ['ObjectTypes']]),
            'relatedEntities' => [
                $this->Media->get(10, ['contain' => ['ObjectTypes']])->set(['_joinData' => ['params' => ['description' => []]]]),
                $this->Media->get(14, ['contain' => ['ObjectTypes']]),
            ],
        ]);

        // Save with placeholder in body.
        $document = $this->Documents->get(2, ['contain' => ['ObjectTypes', 'Placeholder']]);
        static::assertSame([10, 14], Hash::extract($document->get('placeholder'), '{n}.id')); // Check that previous placeholders exist.
        $document->body = $body;
        $this->Documents->saveOrFail($document);

        // Reload entity from database, with placeholders.
        $document = $this->Documents->get(2, ['contain' => ['ObjectTypes', 'Placeholder']]);
        static::assertSame($body, $document->body, 'Entity body has been changed');
        static::assertTrue($document->has('placeholder'));

        // Run assertions.
        $placeholders = $document->get('placeholder');
        $ids = Hash::extract($placeholders, '{n}.id');
        static::assertSame(array_keys($expected), $ids);
        foreach ($placeholders as $placeholder) {
            $id = Hash::get($placeholder, 'id');
            $params = Hash::get($placeholder, '_joinData.params');

            static::assertSame($expected[$id], $params);
        }
    }

    /**
     * Test {@see PlaceholdersBehavior::afterSave()}.
     *
     * @return void
     *
     * @covers ::afterSave()
     * @covers ::getAssociation()
     * @covers ::prepareEntities()
     */
    public function testSavePlaceholdersRemove(): void
    {
        $body = '<h1>My sweet placeholder</h1>';

        // Save hypothetical previous data.
        $action = new AddRelatedObjectsAction(['association' => $this->Documents->getAssociation('Placeholder')]);
        $action([
            'entity' => $this->Documents->get(2, ['contain' => ['ObjectTypes']]),
            'relatedEntities' => [
                $this->Media->get(10, ['contain' => ['ObjectTypes']])->set(['_joinData' => ['params' => ['description' => []]]]),
                $this->Media->get(14, ['contain' => ['ObjectTypes']]),
            ],
        ]);

        // Save with placeholder in body.
        $document = $this->Documents->get(2, ['contain' => ['ObjectTypes', 'Placeholder']]);
        static::assertSame([10, 14], Hash::extract($document->get('placeholder'), '{n}.id')); // Check that previous placeholders exist.
        $document->body = $body;
        $this->Documents->saveOrFail($document);

        // Reload entity from database, with placeholders.
        $document = $this->Documents->get(2, ['contain' => ['ObjectTypes', 'Placeholder']]);
        static::assertSame($body, $document->body, 'Entity body has been changed');
        static::assertTrue($document->has('placeholder'));

        // Run assertions.
        $placeholders = $document->get('placeholder');
        $ids = Hash::extract($placeholders, '{n}.id');
        static::assertSame([], $ids);
    }
}
