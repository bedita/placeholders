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

use BEdita\API\Controller\ObjectsController;
use BEdita\Core\Model\Table\ObjectsBaseTable;
use BEdita\Core\Model\Table\ObjectsTable;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;

/**
 * Attach placeholders behavior and component to relevant models and controllers, respectively, upon initialization.
 */
class BootstrapEventHandler implements EventListenerInterface
{
    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function implementedEvents(): array
    {
        return [
            'Controller.initialize' => 'onControllerInitialize',
            'Model.initialize' => 'onModelInitialize',
        ];
    }

    /**
     * Attach placeholders component on BEdita API controllers.
     *
     * @param \Cake\Event\Event<\Cake\Controller\Controller> $event Dispatched event.
     * @return void
     */
    public function onControllerInitialize(Event $event): void
    {
        $controller = $event->getSubject();
        if (!$controller instanceof ObjectsController) {
            return;
        }

        $controller->loadComponent('BEdita/Placeholders.Placeholders');
    }

    /**
     * Attach placeholders behavior on BEdita objects tables.
     *
     * @param \Cake\Event\Event<\Cake\ORM\Table> $event Dispatched event.
     * @return void
     */
    public function onModelInitialize(Event $event): void
    {
        $table = $event->getSubject();
        if (!$table instanceof ObjectsTable && !$table instanceof ObjectsBaseTable) {
            return;
        }

        $table
            ->addBehavior('BEdita/Placeholders.Placeholders')
            ->addBehavior('BEdita/Placeholders.Placeholded');
    }
}
