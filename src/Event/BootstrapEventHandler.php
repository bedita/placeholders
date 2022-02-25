<?php

namespace BEdita\Placeholders\Event;

use BEdita\API\Controller\ObjectsController;
use BEdita\Core\Model\Entity\ObjectType;
use BEdita\Core\Model\Table\ObjectsBaseTable;
use BEdita\Core\Model\Table\ObjectsTable;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Attach placeholders behavior and component to relevant models and controllers, respectively, upon initialization.
 */
class BootstrapEventHandler implements EventListenerInterface
{
    use LocatorAwareTrait;

    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [
            'Model.initialize' => 'onModelInitialize',
            'Controller.initialize' => 'onControllerInitialize',
            'ObjectType.getSchema' => 'onGetSchema',
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

    /**
     * Modify object type schema by marking fields where placeholders are read from.
     *
     * @param \Cake\Event\Event $event Dispatched event.
     * @param array $schema Automatically generated JSON schema.
     * @param \BEdita\Core\Model\Entity\ObjectType $objectType Object type.
     * @return array
     */
    public function onGetSchema(Event $event, array $schema, ObjectType $objectType): array
    {
        $table = $this->getTableLocator()->get($objectType->table);
        if (!$table->hasBehavior('Placeholders')) {
            return $schema;
        }

        $fields = $table->getBehavior('Placeholders')->getConfig('fields');
        foreach ($fields as $field) {
            if (!isset($schema['properties'][$field])) {
                continue;
            }
            $schema['properties'][$field]['placeholders'] = true;
        }

        return $schema;
    }
}
