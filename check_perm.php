<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(1);
echo "User: " . $user->name . PHP_EOL;
echo "Email: " . $user->email . PHP_EOL;
echo "Admin: " . ($user->isAdmin() ? 'SI' : 'NO') . PHP_EOL;
echo "HasPermisos ver_palomar: " . ($user->HasPermisos('ver_palomar') ? 'SI' : 'NO') . PHP_EOL;

// Test the policy directly
$gate = app(\Illuminate\Contracts\Auth\Access\Gate::class);

// Use reflection to trace the Gate's internal raw() call
$rawMethod = new ReflectionMethod($gate, 'raw');
$rawMethod->setAccessible(true);

echo "--- Tracing Gate::raw() ---" . PHP_EOL;
$rawResult = $rawMethod->invoke($gate, 'viewAny', [App\Models\Palomar::class]);
echo "raw() returned type: " . gettype($rawResult) . PHP_EOL;
if (is_object($rawResult)) {
    echo "raw() returned class: " . get_class($rawResult) . PHP_EOL;
    if (method_exists($rawResult, 'allow')) {
        echo "raw() Response->allow(): " . ($rawResult->allow() ? 'true' : 'false') . PHP_EOL;
    }
} else {
    echo "raw() returned value: " . var_export($rawResult, true) . PHP_EOL;
}

echo "--- Tracing Gate::inspect() ---" . PHP_EOL;
$inspectResult = $gate->inspect('viewAny', App\Models\Palomar::class);
echo "inspect() returned class: " . get_class($inspectResult) . PHP_EOL;
echo "inspect() Response->allow(): " . ($inspectResult->allow() ? 'true' : 'false') . PHP_EOL;

echo "--- Tracing Response::authorize() ---" . PHP_EOL;
try {
    $inspectResult->authorize();
    echo "Response->authorize(): PASSED" . PHP_EOL;
} catch (\Exception $e) {
    echo "Response->authorize(): FAILED - " . $e->getMessage() . PHP_EOL;
}

// Check the Response internal state
echo "--- Response internals ---" . PHP_EOL;
$respProp = new ReflectionProperty($inspectResult, 'allowed');
$respProp->setAccessible(true);
echo "Response internal 'allowed': " . var_export($respProp->getValue($inspectResult), true) . PHP_EOL;
$msgProp = new ReflectionProperty($inspectResult, 'message');
$msgProp->setAccessible(true);
echo "Response internal 'message': " . var_export($msgProp->getValue($inspectResult), true) . PHP_EOL;
$codeProp = new ReflectionProperty($inspectResult, 'code');
$codeProp->setAccessible(true);
echo "Response internal 'code': " . var_export($codeProp->getValue($inspectResult), true) . PHP_EOL;
