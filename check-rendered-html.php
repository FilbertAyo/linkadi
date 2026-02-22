<?php
/**
 * Check what HTML is actually being rendered
 * Run: php check-rendered-html.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate authenticated user (you may need to adjust this)
$package = \App\Models\Package::where('slug', 'nfc-card')->first();

if (!$package) {
    die("Package not found!\n");
}

echo "=== Package Data ===\n";
echo "ID: " . $package->id . "\n";
echo "Card colors from DB: ";
var_dump($package->card_colors);
echo "\n";

echo "getAvailableCardColors(): ";
$colors = $package->getAvailableCardColors();
var_dump($colors);
echo "\n";

echo "=== JSON Output (what JavaScript receives) ===\n";
$jsonOutput = json_encode($colors);
echo $jsonOutput . "\n\n";

echo "=== What should be in HTML ===\n";
echo "availableColors: " . $jsonOutput . "\n\n";

echo "=== Check Compiled Views ===\n";
$viewFiles = glob(__DIR__ . '/storage/framework/views/*.php');
if (count($viewFiles) > 0) {
    echo "Found " . count($viewFiles) . " compiled view files\n";
    echo "Checking most recent ones for 'availableColors'...\n";
    
    $found = false;
    foreach (array_slice($viewFiles, -5) as $viewFile) {
        $content = file_get_contents($viewFile);
        if (strpos($content, 'availableColors') !== false) {
            echo "\nFound in: " . basename($viewFile) . "\n";
            // Extract the line with availableColors
            $lines = explode("\n", $content);
            foreach ($lines as $num => $line) {
                if (strpos($line, 'availableColors') !== false) {
                    echo "Line " . ($num + 1) . ": " . trim($line) . "\n";
                    $found = true;
                }
            }
        }
    }
    
    if (!$found) {
        echo "No compiled views contain 'availableColors' (they will be regenerated)\n";
    }
} else {
    echo "No compiled views found\n";
}

echo "\n=== Recommendation ===\n";
echo "1. Clear all compiled views: rm -rf storage/framework/views/*.php\n";
echo "2. Clear view cache: php artisan view:clear\n";
echo "3. Visit the page and check View Source for 'availableColors'\n";
