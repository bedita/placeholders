<?php

namespace BEdita\Placeholders\Test\Fixture;

use BEdita\Core\TestSuite\Fixture\TestFixture;

/**
 * Fixture for `relations` table.
 */
class RelationsFixture extends TestFixture
{
    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'name' => 'poster',
            'label' => 'Poster',
            'inverse_name' => 'poster_od',
            'inverse_label' => 'Poster of',
            'description' => 'Poster image.',
            'params' => null,
        ],
        [
            'name' => 'placeholder',
            'label' => 'Placeholder',
            'inverse_name' => 'placeholded',
            'inverse_label' => 'Placeholded in',
            'description' => 'Relation to link objects with other objects that appear in the body of the former.',
            'params' => null,
        ],
    ];
}
