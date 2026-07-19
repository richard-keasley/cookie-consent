<?php

/**
 * Example Integration of Cookie Consent with Banner
 * 
 * This example shows how to integrate the CookieConsent class
 * with the HTML/CSS/JavaScript banner in your application.
 */

require_once 'consent.php';

// Initialize the consent handler
$consent = new CookieConsent();

// Check if user has already given consent
$hasConsent = $consent->hasGivenConsent();
$preferences = $consent->getPreferences();

// Handle AJAX POST requests to save preferences
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['CONTENT_TYPE'] === 'application/json') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (is_array($input)) {
        $consent->setConsent($input, save: true);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Preferences saved']);
        exit;
    }
}

// Example: Check if banner should be shown
$showBanner = !$hasConsent || $consent->needsRenewal();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cookie Consent Example</title>
    <link rel="stylesheet" href="banner.css">
</head>
<body>
    <!-- Your website content here -->
    <div class="container" style="padding: 40px; max-width: 1000px; margin: 0 auto;">
        <h1>Welcome to Our Website</h1>
        <p>This is an example page showing the cookie consent banner integration.</p>

        <?php if ($showBanner): ?>
            <!-- Cookie Consent Banner -->
            <?php include 'banner.html'; ?>
        <?php endif; ?>

        <!-- Example content showing consent status -->
        <div style="margin-top: 40px; padding: 20px; background: #f0f0f0; border-radius: 8px;">
            <h2>Current Consent Status</h2>
            <p><strong>Consent Given:</strong> <?php echo $hasConsent ? 'Yes' : 'No'; ?></p>
            <p><strong>Needs Renewal:</strong> <?php echo $consent->needsRenewal() ? 'Yes' : 'No'; ?></p>
            <p><strong>Preferences:</strong></p>
            <ul>
                <?php foreach ($consent->getCategories() as $category => $description): ?>
                    <li>
                        <strong><?php echo ucfirst($category); ?>:</strong>
                        <?php echo $consent->hasConsented($category) ? 'Enabled' : 'Disabled'; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Example: Display personalized content based on consent -->
        <div style="margin-top: 40px;">
            <h2>Content Examples</h2>
            
            <?php if ($consent->hasConsented('analytics')): ?>
                <div style="padding: 10px; background: #e8f5e9; border-radius: 4px; margin-bottom: 10px;">
                    <strong>✓ Analytics:</strong> Your usage data will be tracked and analyzed.
                </div>
            <?php endif; ?>

            <?php if ($consent->hasConsented('marketing')): ?>
                <div style="padding: 10px; background: #e8f5e9; border-radius: 4px; margin-bottom: 10px;">
                    <strong>✓ Marketing:</strong> You may see personalized ads.
                </div>
            <?php endif; ?>

            <?php if ($consent->hasConsented('preferences')): ?>
                <div style="padding: 10px; background: #e8f5e9; border-radius: 4px; margin-bottom: 10px;">
                    <strong>✓ Preferences:</strong> Your settings will be saved for next visit.
                </div>
            <?php endif; ?>
        </div>

        <!-- Show audit log for compliance -->
        <div style="margin-top: 40px; padding: 20px; background: #f5f5f5; border-radius: 8px;">
            <h2>Audit Log (for compliance records)</h2>
            <pre style="background: white; padding: 10px; border-radius: 4px; overflow-x: auto;">
<?php echo $consent->exportAsJson(); ?>
            </pre>
        </div>
    </div>

    <!-- Load the banner JavaScript -->
    <script src="banner.js"></script>
    
    <!-- Optional: Listen to consent changes in your application -->
    <script>
        window.addEventListener('consentPreferencesChanged', function(event) {
            console.log('User consent preferences:', event.detail);
            
            // Example: Load analytics script only if user consented
            if (event.detail.analytics) {
                console.log('User consented to analytics - you can now load GA, Segment, etc.');
                // loadAnalyticsScript();
            }
            
            // Example: Enable marketing cookies only if user consented
            if (event.detail.marketing) {
                console.log('User consented to marketing - you can now enable ads');
                // loadMarketingScripts();
            }
        });
    </script>
</body>
</html>
