<?php

declare(strict_types=1);

/**
 * Example: Repository pattern with l5-repository (prettus/l5-repository).
 *
 * This demonstrates the API without a running Laravel application.
 * In real usage, repositories extend BaseRepository and are bound in a
 * ServiceProvider.
 *
 * Run from the l5-repository project root:
 *   php examples/repository_pattern.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Prettus\Repository\Contracts\Repository_Interface;
use Prettus\Repository\Contracts\Criteria_Interface;
use Prettus\Repository\Criteria\Request_Criteria;

// --- Show the Repository_Interface contract ---
echo "Repository_Interface methods:\n";
$methods = (new ReflectionClass(Repository_Interface::class))->getMethods();
foreach ($methods as $method) {
    printf("  %s(%s)\n",
        $method->getName(),
        implode(', ', array_map(
            fn($p) => ($p->hasType() ? $p->getType() . ' ' : '') . '$' . $p->getName(),
            $method->getParameters()
        ))
    );
}

echo "\n--- Criteria pattern ---\n";
echo "Criteria_Interface: " . Criteria_Interface::class . "\n";
echo "Request_Criteria:   " . Request_Criteria::class   . "\n";

echo "\nTypical repository definition:\n";
echo <<<'PHP'
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function model(): string
    {
        return User::class;
    }
}

// Usage:
$repo->all(['id', 'name', 'email']);
$repo->find(42);
$repo->findBy_field('email', 'alice@example.com');
$repo->create(['name' => 'Alice', 'email' => 'alice@example.com']);
$repo->update(['name' => 'Alice B.'], 42);
$repo->delete(42);

// Criteria:
$repo->push_criteria(new RequestCriteria($request));
$repo->push_criteria(new ActiveUsersCriteria());
$users = $repo->all();
PHP;
echo "\n";
