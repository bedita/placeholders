<?php
declare(strict_types=1);

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

use BEdita\Core\Exception\LockedResourceException;
use BEdita\Placeholders\Event\BootstrapEventHandler;
use Cake\Event\EventManager;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;

/**
 * {@see \BEdita\Placeholders\Model\Behavior\PlaceholdedBehavior} Test Case
 *
 * @coversDefaultClass \BEdita\Placeholders\Model\Behavior\PlaceholdedBehavior
 * @property \BEdita\Core\Model\Table\ObjectsTable $Documents
 * @property \BEdita\Core\Model\Table\MediaTable $Media
 */
class PlaceholdedBehaviorTest extends TestCase
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
     * ObjectsTable instance
     *
     * @var \BEdita\Core\Model\Table\ObjectsTable
     */
    protected $Documents = null;

    /**
     * MediaTable instance
     *
     * @var \BEdita\Core\Model\Table\MediaTable
     */
    protected $Media = null;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        EventManager::instance()->on(new BootstrapEventHandler());

        /** @var \BEdita\Core\Model\Table\ObjectsTable $documents */
        $documents = $this->fetchTable('Documents');
        $this->Documents = $documents;
        /** @var \BEdita\Core\Model\Table\MediaTable $media */
        $media = $this->fetchTable('Media');
        $this->Media = $media;
    }

    /**
     * Test {@see PlaceholdedBehavior::beforeSave()}.
     *
     * @return void
     * @covers ::beforeSave()
     * @covers ::getAssociation()
     * @covers ::ensureNotPlaceholded()
     */
    public function testBeforeSaveLockedEntity(): void
    {
        $body = '<!-- BE-PLACEHOLDER.10 --><h1>My sweet placeholder</h1>';

        // Save with placeholder in body.
        $document = $this->Documents->get(2, ['contain' => ['ObjectTypes']]);
        $document->body = $body;
        $this->Documents->saveOrFail($document);

        $document = $this->Documents->get(2, ['contain' => ['ObjectTypes', 'Placeholder']]);
        static::assertSame([10], Hash::extract($document->get('placeholder'), '{n}.id'));

        // Try to delete media.
        $this->expectException(LockedResourceException::class);
        $this->expectExceptionMessage('Cannot delete object 10 because it is still placeholded in one object');
        $media = $this->Media->get(10, ['contain' => ['ObjectTypes']]);
        $media->deleted = true;
        $this->Media->saveOrFail($media);
    }

    /**
     * Test {@see PlaceholdedBehavior::beforeSave()} with an entity that is not placeholded anywhere.
     *
     * @return void
     * @covers ::beforeSave()
     * @covers ::getAssociation()
     * @covers ::ensureNotPlaceholded()
     */
    public function testBeforeSaveFreeEntity(): void
    {
        $media = $this->Media->get(10, ['contain' => ['ObjectTypes']]);
        $media->deleted = true;
        $actual = $this->Media->saveOrFail($media);

        static::assertSame(true, $actual->get('deleted'));
    }
}
