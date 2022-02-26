<?php

namespace BEdita\Placeholders\Test\Fixture;

use BEdita\Core\TestSuite\Fixture\TestFixture;

/**
 * Fixture for `relation_types` table.
 */
class RelationTypesFixture extends TestFixture
{
    /**
     * Records
     *
     * @var array
     */
    public $records = [
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

    /**
     * Before Build Schema callback
     *
     * Change `side` type to 'string' to avoid errors
     *
     * @return void
     */
    public function beforeBuildSchema()
    {
        $this->fields['side']['type'] = 'string';
    }
}
