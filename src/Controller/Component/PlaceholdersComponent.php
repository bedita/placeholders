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

namespace BEdita\Placeholders\Controller\Component;

use Cake\Controller\Component;
use Cake\Http\Exception\ForbiddenException;

/**
 * Placeholders component
 */
class PlaceholdersComponent extends Component
{
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'relations' => ['placeholder', 'placeholded'],
    ];

    /**
     * Block POST|PATCH|DELETE requests on `/objects/{id}/relationships/(placeholder|placeholded)` endpoints.
     *
     * @return void
     */
    public function beforeFilter(): void
    {
        $request = $this->getController()->getRequest();
        if (
            $request->getParam('action') !== 'relationships' ||
            !in_array($request->getParam('relationship'), (array)$this->getConfig('relations'))
        ) {
            return;
        }
        if (!in_array(mb_strtoupper($request->getMethod()), ['GET', 'HEAD', 'OPTIONS'])) {
            throw new ForbiddenException(
                __d(
                    'placeholders',
                    'Relationships of type {0} can only be managed saving an object',
                    $request->getParam('relationship')
                )
            );
        }
    }
}
