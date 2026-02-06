<?php
/**
 * Check what's actually being rendered in the page
 * Place this in public/check-page-source.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate the checkout page data
$package = \App\Models\Package::where('slug', 'nfc-card')->first();

if (!$package) {
    die("Package 'nfc-card' not found!");
}

echo "<h2>Page Source Diagnostic</h2>";
echo "<pre>";

echo "=== Database Values ===\n";
echo "card_colors (raw): ";
var_dump($package->card_colors);
echo "\n";

echo "getAvailableCardColors(): ";
$colors = $package->getAvailableCardColors();
var_dump($colors);
echo "\n";

echo "=== What JavaScript Will Receive ===\n";
echo "@json() output: ";
echo json_encode($colors);
echo "\n\n";

echo "=== Full JavaScript Init Code ===\n";
echo "availableColors: " . json_encode($colors) . "\n\n";

echo "=== Check Blade Template ===\n";
$bladePath = __DIR__ . '/../resources/views/client/cards/checkout.blade.php';
if (file_exists($bladePath)) {
    $bladeContent = file_get_contents($bladePath);
    if (strpos($bladeContent, '@json($package->getAvailableCardColors())') !== false) {
        echo "✓ Blade template has correct code\n";
    } else {
        echo "✗ Blade template might be outdated\n";
    }
    
    // Check if it has the old fallback
    if (strpos($bladeContent, "['black', 'white', 'silver', 'gold', 'blue']") !== false) {
        echo "✗ WARNING: Blade template has hardcoded colors!\n";
    }
} else {
    echo "✗ Blade template not found\n";
}

echo "\n=== Compiled View Check ===\n";
$compiledViews = glob(__DIR__ . '/../storage/framework/views/*.php');
if (count($compiledViews) > 0) {
    echo "Found " . count($compiledViews) . " compiled views\n";
    echo "Most recent compiled view: " . basename(end($compiledViews)) . "\n";
} else {
    echo "No compiled views found\n";
}

echo "</pre>";

echo "<h3>Test JavaScript Output</h3>";
echo "<script>";
echo "var testColors = " . json_encode($colors) . ";\n";
echo "console.log('Test colors:', testColors);\n";
echo "console.log('Is array:', Array.isArray(testColors));\n";
echo "console.log('Length:', testColors.length);\n";
echo "console.log('Colors:', testColors);\n";
echo "</script>";
echo "<p>Check browser console (F12) for the test output above.</p>";
