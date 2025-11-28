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

use Cake\ORM\Association;
use Cake\ORM\Table;
use Cake\Utility\Inflector;

/**
 * Trait to get association from relation name.
 */
trait GetAssociationTrait
{
    /**
     * Getter for Table object.
     *
     * @return \Cake\ORM\Table
     */
    abstract public function table(): Table;

    /**
     * Get association for a relation.
     *
     * @param string $relation Relation name.
     * @return \Cake\ORM\Association|null
     */
    protected function getAssociation(string $relation): ?Association
    {
        $name = Inflector::camelize($relation);
        $table = $this->table();
        if (!$table->hasAssociation($name)) {
            return null;
        }

        return $table->getAssociation($name);
    }
}
