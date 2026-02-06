<?php
/**
 * Quick diagnostic script to check card colors
 * Place this in public/check-colors.php and visit: https://linkadi.co.tz/check-colors.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$package = \App\Models\Package::where('slug', 'nfc-card')->first();

if (!$package) {
    die("Package 'nfc-card' not found!");
}

echo "<h2>Card Colors Diagnostic</h2>";
echo "<pre>";
echo "Package ID: " . $package->id . "\n";
echo "Package Name: " . $package->name . "\n";
echo "Package Slug: " . $package->slug . "\n\n";

echo "Raw card_colors from database:\n";
var_dump($package->card_colors);
echo "\n";

echo "getAvailableCardColors() result:\n";
$colors = $package->getAvailableCardColors();
var_dump($colors);
echo "\n";

echo "JSON encoded (what JavaScript receives):\n";
echo json_encode($colors, JSON_PRETTY_PRINT);
echo "\n\n";

echo "Is array: " . (is_array($colors) ? 'YES' : 'NO') . "\n";
echo "Count: " . count($colors) . "\n";
echo "Colors: " . implode(', ', $colors) . "\n";
echo "</pre>";
