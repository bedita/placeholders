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

namespace BEdita\Placeholders\Event;

use BEdita\Core\Model\Entity\ObjectType;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Modify generated JSON schema for object types to mark fields where placeholders are read from.
 */
class JsonSchemaEventHandler implements EventListenerInterface
{
    use LocatorAwareTrait;

    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [
            'ObjectType.getSchema' => 'onGetSchema',
        ];
    }

    /**
     * Modify object type schema by marking fields where placeholders are read from.
     *
     * @param \Cake\Event\Event<\BEdita\Core\Model\Table\ObjectTypesTable> $event Dispatched event.
     * @param array<string, mixed> $schema Automatically generated JSON schema.
     * @param \BEdita\Core\Model\Entity\ObjectType $objectType Object type.
     * @return array<string, mixed>
     */
    public function onGetSchema(Event $event, array $schema, ObjectType $objectType): array
    {
        $table = $this->getTableLocator()->get($objectType->table);
        if (!$table->hasBehavior('Placeholders')) {
            return $schema;
        }

        $behavior = $table->getBehavior('Placeholders');

        // Mark fields where placeholders are read from.
        $fields = $behavior->getConfig('fields', []);
        foreach ($fields as $field) {
            if (!isset($schema['properties'][$field])) {
                continue;
            }
            $schema['properties'][$field]['placeholders'] = true;
        }

        // Mark relevant relations as read only.
        $relations = [$behavior->getConfig('relation')];
        if ($table->hasBehavior('Placeholded')) {
            $placeholdedBehavior = $table->getBehavior('Placeholded');
            array_push($relations, ...$placeholdedBehavior->getConfig('relations', []));
        }
        foreach ($relations as $relName) {
            if (!isset($schema['relations'][$relName])) {
                continue;
            }
            $schema['relations'][$relName]['readonly'] = true;
        }

        return $schema;
    }
}
