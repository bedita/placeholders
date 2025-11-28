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

namespace BEdita\Placeholders\Model\Behavior;

use BEdita\Core\Exception\LockedResourceException;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\Utility\Hash;

/**
 * Placeholded behavior
 */
class PlaceholdedBehavior extends Behavior
{
    use GetAssociationTrait;

    /**
     * Default configurations. Available configurations include:
     *
     * - `relations`: names of the BEdita relations to check.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'relations' => ['placeholded'],
    ];

    /**
     * Lock entity from being soft-deleted if it is placeholded somewhere.
     *
     * @param \Cake\Event\Event $event Dispatched event.
     * @param \Cake\Datasource\EntityInterface $entity Entity being saved.
     * @return void
     */
    public function beforeSave(Event $event, EntityInterface $entity): void
    {
        if (!$entity->isDirty('deleted') || !$entity->get('deleted')) {
            return;
        }

        $this->ensureNotPlaceholded($entity);
    }

    /**
     * Ensure an entity does not appear as a placeholder.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity being checked.
     * @return void
     * @throws \BEdita\Core\Exception\LockedResourceException
     */
    protected function ensureNotPlaceholded(EntityInterface $entity): void
    {
        $Table = $this->table();

        $relations = $this->getConfig('relations', []);
        foreach ($relations as $relation) {
            $association = $this->getAssociation($relation);
            if ($association === null) {
                continue;
            }

            $refCount = $Table->find()
                ->select(['existing' => 1])
                ->where((array)array_combine(
                    array_map([$Table, 'aliasField'], (array)$Table->getPrimaryKey()),
                    $entity->extract((array)$Table->getPrimaryKey()),
                ))
                ->innerJoinWith($association->getName())
                ->count();
            if ($refCount > 0) {
                throw new LockedResourceException(__d(
                    'placeholders',
                    'Cannot delete object {0} because it is still {1} in {2,plural,=1{one object} other{# objects}}',
                    (string)Hash::get($entity, 'id'),
                    $relation,
                    $refCount,
                ));
            }
        }
    }
}
