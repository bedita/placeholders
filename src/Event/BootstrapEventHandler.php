<?php

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
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [
            'Model.initialize' => 'onModelInitialize',
            'Controller.initialize' => 'onControllerInitialize',
        ];
    }

    /**
     * Attach placeholders behavior on BEdita objects tables.
     *
     * @param \Cake\Event\Event $event Dispatched event.
     * @return void
     */
    public function onModelInitialize(Event $event): void
    {
        $table = $event->getSubject();
        if (!$table instanceof ObjectsTable && !$table instanceof ObjectsBaseTable) {
            return;
        }

        $table->addBehavior('BEdita/Placeholders.Placeholders');
    }

    /**
     * Attach placeholders component on BEdita API controllers.
     *
     * @param \Cake\Event\Event $event Dispatched event.
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
}
