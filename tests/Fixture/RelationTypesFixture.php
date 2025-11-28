<?php
declare(strict_types=1);

namespace BEdita\Placeholders\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Fixture for `relation_types` table.
 */
class RelationTypesFixture extends TestFixture
{
    /**
     * Records
     *
     * @var array<int, array<string, mixed>>
     */
    public array $records = [
        [
            'relation_id' => 1, // poster / poster_of
            'object_type_id' => 1, // objects
            'side' => 'left',
        ],
        [
            'relation_id' => 2, // poster / poster_of
            'object_type_id' => 8, // media
            'side' => 'right',
        ],
        [
            'relation_id' => 2, // placeholder / placeholded
            'object_type_id' => 1, // objects
            'side' => 'left',
        ],
        [
            'relation_id' => 2, // placeholder / placeholded
            'object_type_id' => 1, // objects
            'side' => 'right',
        ],
    ];
}
