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

namespace BEdita\Placeholders\Event;

use BEdita\Core\Model\Entity\ObjectType;
use Cake\Event\Event;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Modify generated JSON schema for object types to mark fields where placeholders are read from.
 */
class JsonSchemaEventHandler implements \Cake\Event\EventListenerInterface
{
    use LocatorAwareTrait;

    /**
     * @inheritDoc
     */
    public function implementedEvents()
    {
        return [
            'ObjectType.getSchema' => 'onGetSchema',
        ];
    }

    /**
     * Modify object type schema by marking fields where placeholders are read from.
     *
     * @param \Cake\Event\Event $event Dispatched event.
     * @param array $schema Automatically generated JSON schema.
     * @param \BEdita\Core\Model\Entity\ObjectType $objectType Object type.
     * @return array
     */
    public function onGetSchema(Event $event, array $schema, ObjectType $objectType): array
    {
        $table = $this->getTableLocator()->get($objectType->table);
        if (!$table->hasBehavior('Placeholders')) {
            return $schema;
        }

        $fields = $table->getBehavior('Placeholders')->getConfig('fields');
        foreach ($fields as $field) {
            if (!isset($schema['properties'][$field])) {
                continue;
            }
            $schema['properties'][$field]['placeholders'] = true;
        }

        return $schema;
    }
}
