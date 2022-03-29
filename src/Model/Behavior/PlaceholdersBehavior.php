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

namespace BEdita\Placeholders\Model\Behavior;

use BEdita\Core\Exception\LockedResourceException;
use BEdita\Core\Model\Action\SetRelatedObjectsAction;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Association;
use Cake\ORM\Behavior;
use Cake\ORM\Table;
use InvalidArgumentException;
use RuntimeException;

/**
 * Placeholders behavior
 */
class PlaceholdersBehavior extends Behavior
{
    use GetAssociationTrait;

    /**
     * The default regex to use to interpolate placeholders data.
     *
     * @var string
     */
    protected const REGEX = '/<!--\s*BE-PLACEHOLDER\.(?P<id>\d+)(?:\.(?P<params>[A-Za-z0-9+=-]+))?\s*-->/';

    /**
     * Default configurations. Available configurations include:
     *
     * - `relation`: name of the BEdita relation to use.
     * - `fields`: list of fields from which placeholders should be extracted.
     * - `extract`: extract function that will be called on each entity; it will receive
     *      the entity instance and an array of fields as input, and is expected to return
     *      a list of associative arrays with `id` and `params` fields.
     *      If `null`, uses {@see \BEdita\Core\Model\Behavior\PlaceholdersBehavior::extractPlaceholders()}.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'relation' => 'placeholder',
        'fields' => ['description', 'body'],
        'extract' => null,
    ];

    /**
     * Extract placeholders from an entity.
     *
     * @param EntityInterface $entity The entity from which to extract placeholder references.
     * @param string[] $fields Field names.
     * @return array[] A list of arrays, each with `id` and `params` set.
     */
    public static function extractPlaceholders(EntityInterface $entity, array $fields): array
    {
        $placeholders = [];
        foreach ($fields as $field) {
            $datum = $entity->get($field);
            if (empty($datum)) {
                continue;
            }

            if (!is_string($datum) || preg_match_all(static::REGEX, $datum, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) === false) {
                throw new RuntimeException(__d('bedita', 'Error extracting placeholders'));
            }

            foreach ($matches as $match) {
                $offsetBytes = $match[0][1]; // This is the offset in bytes!!
                $offset = mb_strlen(substr($datum, 0, $offsetBytes)); // Turn bytes offset into character offset.
                $length = mb_strlen($match[0][0]);
                $id = (int)$match['id'][0];
                $params = null;
                if (!empty($match['params'][0])) {
                    $params = base64_decode($match['params'][0]);
                }

                if (!isset($placeholders[$id])) {
                    $placeholders[$id] = [
                        'id' => $id,
                        'params' => [],
                    ];
                }
                $placeholders[$id]['params'][$field][] = compact('offset', 'length', 'params');
            }
        }

        return array_values($placeholders);
    }

    /**
     * Add associations using placeholder relation.
     *
     * @param \Cake\Event\Event $event Fired event.
     * @param \Cake\Datasource\EntityInterface $entity Entity.
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $entity): void
    {
        $association = $this->getAssociation($this->getConfigOrFail('relation'));
        $fields = $this->getConfig('fields', []);
        $anyDirty = array_reduce(
            $fields,
            function (bool $isDirty, string $field) use ($entity): bool {
                return $isDirty || $entity->isDirty($field);
            },
            false
        );
        if ($association === null || $anyDirty === false) {
            // Nothing to do.
            return;
        }
        if (!in_array($association->type(), [Association::ONE_TO_MANY, Association::MANY_TO_MANY])) {
            throw new InvalidArgumentException(sprintf('Invalid association type "%s"', get_class($association)));
        }

        $extract = $this->getConfig('extract', [static::class, 'extractPlaceholders']);
        $placeholders = $extract($entity, $fields);
        $relatedEntities = $this->prepareEntities($association->getTarget(), $placeholders);

        $action = new SetRelatedObjectsAction(compact('association'));
        $action(compact('entity', 'relatedEntities'));
    }

    /**
     * Prepare target entities.
     *
     * @param \Cake\ORM\Table $table Target table.
     * @param array[] $placeholders Placeholders data.
     * @return \Cake\Datasource\EntityInterface[]
     */
    protected function prepareEntities(Table $table, array $placeholders): array
    {
        /** @var string $pk */
        $pk = $table->getPrimaryKey();
        $ids = array_column($placeholders, 'id');
        if (empty($ids)) {
            return [];
        }

        $fields = [$table->aliasField($pk)];
        if ($table->hasAssociation('ObjectTypes')) {
            /** @var string $fk */
            $fk = $table->getAssociation('ObjectTypes')->getForeignKey();
            $fields = array_merge($fields, [$table->aliasField($fk)]);
        }

        return $table->find()
            ->select($fields)
            ->where(function (QueryExpression $exp) use ($table, $pk, $ids): QueryExpression {
                return $exp->in($table->aliasField($pk), $ids);
            })
            ->map(function (EntityInterface $entity) use ($pk, $placeholders): EntityInterface {
                $id = $entity->get($pk);
                foreach ($placeholders as $datum) {
                    if ($datum['id'] == $id) {
                        $entity->set('_joinData', [
                            'params' => $datum['params'],
                        ]);

                        break;
                    }
                }

                return $entity;
            })
            ->toList();
    }
}
