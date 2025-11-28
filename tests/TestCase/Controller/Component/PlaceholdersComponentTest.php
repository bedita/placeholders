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

namespace BEdita\Placeholders\Test\TestCase\Controller\Component;

use BEdita\Placeholders\Controller\Component\PlaceholdersComponent;
use Cake\Controller\Controller;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * {@see \BEdita\Placeholders\Controller\Component\PlaceholdersComponent} Test Case
 */
#[CoversClass(PlaceholdersComponent::class)]
class PlaceholdersComponentTest extends TestCase
{
    /**
     * Controller instance.
     *
     * @var \Cake\Controller\Controller
     */
    protected Controller $controller;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->controller = new Controller(new ServerRequest());
        $this->controller->loadComponent(PlaceholdersComponent::class);
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->controller);

        parent::tearDown();
    }

    /**
     * Data provider for {@see PlaceholdersComponentTest::testBeforeFilter()} test case.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function beforeFilterProvider(): array
    {
        $request = new ServerRequest();

        return [
            'another action' => [
                null,
                $request
                    ->withMethod('POST')
                    ->withParam('action', 'index')
                    ->withParam('relationship', 'placeholders'),
            ],
            'another relation' => [
                null,
                $request
                    ->withMethod('POST')
                    ->withParam('action', 'relationships')
                    ->withParam('relationship', 'poster'),
            ],
            'allowed method' => [
                null,
                $request
                    ->withMethod('GET')
                    ->withParam('action', 'relationships')
                    ->withParam('relationship', 'placeholder'),
            ],
            'POST' => [
                new ForbiddenException(__d('placeholders', 'Relationships of type placeholder can only be managed saving an object')),
                $request
                    ->withMethod('POST')
                    ->withParam('action', 'relationships')
                    ->withParam('relationship', 'placeholder'),
            ],
            'PATCH' => [
                new ForbiddenException(__d('placeholders', 'Relationships of type placeholder can only be managed saving an object')),
                $request
                    ->withMethod('PATCH')
                    ->withParam('action', 'relationships')
                    ->withParam('relationship', 'placeholder'),
            ],
            'DELETE' => [
                new ForbiddenException(__d('placeholders', 'Relationships of type placeholder can only be managed saving an object')),
                $request
                    ->withMethod('DELETE')
                    ->withParam('action', 'relationships')
                    ->withParam('relationship', 'placeholder'),
            ],
        ];
    }

    /**
     * Test {@see PlaceholdersComponent::beforeFilter()}.
     *
     * @param \Exception|null $expected Expected exception.
     * @param \Cake\Http\ServerRequest $request Request.
     * @return void
     */
    #[DataProvider('beforeFilterProvider')]
    public function testBeforeFilter(?Exception $expected, ServerRequest $request): void
    {
        if ($expected !== null) {
            $this->expectException(get_class($expected));
            $this->expectExceptionCode($expected->getCode());
            $this->expectExceptionMessage($expected->getMessage());
        }

        $this->controller->setRequest($request);
        $actual = $this->controller->startupProcess();

        static::assertNull($actual);
    }
}
