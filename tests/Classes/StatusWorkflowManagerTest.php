<?php

declare(strict_types=1);

namespace Igniter\Cart\Tests\Classes;

use Igniter\Cart\Classes\StatusWorkflowManager;
use ReflectionMethod;

it('is disabled when status workflow setting is off', function(): void {
    setting()->set(['enable_status_workflow' => false]);

    expect((new StatusWorkflowManager)->isEnabledFor(1))->toBeFalse();
});

it('is enabled for everyone when no user limit is set', function(): void {
    setting()->set([
        'enable_status_workflow' => true,
        'limit_users' => [],
    ]);

    expect((new StatusWorkflowManager)->isEnabledFor(null))->toBeTrue();
});

it('is enabled only for limited users', function(): void {
    setting()->set([
        'enable_status_workflow' => true,
        'limit_users' => [5, 9],
    ]);

    $manager = new StatusWorkflowManager;

    expect($manager->isEnabledFor(5))->toBeTrue()
        ->and($manager->isEnabledFor(1))->toBeFalse()
        ->and($manager->isEnabledFor(null))->toBeFalse();
});

it('returns null delay comment when minutes are not configured', function(): void {
    setting()->set(['delay_times' => []]);

    $method = new ReflectionMethod(StatusWorkflowManager::class, 'delayComment');

    expect($method->invoke(new StatusWorkflowManager, 15))->toBeNull();
});
