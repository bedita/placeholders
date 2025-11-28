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

namespace BEdita\Placeholders;

use BEdita\Placeholders\Event\BootstrapEventHandler;
use BEdita\Placeholders\Event\JsonSchemaEventHandler;
use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;
use Cake\Event\EventManager;

/**
 * Plugin for BEdita\Placeholders
 */
class Plugin extends BasePlugin
{
    /**
     * {@inheritDoc}
     *
     * @param \Cake\Core\PluginApplicationInterface<\Cake\Core\ContainerInterface> $app
     * @codeCoverageIgnore
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        parent::bootstrap($app);

        EventManager::instance()
            ->on(new BootstrapEventHandler())
            ->on(new JsonSchemaEventHandler());
    }
}
