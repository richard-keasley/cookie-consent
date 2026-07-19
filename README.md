# Cookie Consent

A comprehensive GDPR and UK PECR compliant cookie consent management solution with a user-friendly pop-up banner. Handles consent tracking, storage, and renewal with built-in audit logging for compliance records.

## Features

- **GDPR & UK PECR Compliant** - Meets ICO guidelines for cookie consent management
- **Pop-up Banner UI** - Modern, responsive consent banner with granular cookie category controls
- **Multiple Consent Options** - Accept All, Reject All, or customize preferences
- **Secure Storage** - HTTP-only cookies with configurable security flags (Secure, SameSite)
- **Consent Renewal** - Automatic tracking of consent age with renewal prompts (13-month cycle)
- **Audit Logging** - Track user decisions with timestamps, IP addresses, and user agents
- **JSON Export** - Export consent records for compliance documentation
- **No Dependencies** - Vanilla PHP and JavaScript, no external libraries required

## Components

### Backend (PHP)

**File:** `consent.php`

The `CookieConsent` class handles all server-side consent management:

```php
$consent = new CookieConsent();

// Check if user has given consent
if (!$consent->hasGivenConsent()) {
    // Show banner
}

// Save user preferences
$consent->setConsent([
    'analytics' => true,
    'marketing' => false,
    'preferences' => true
]);

// Check if banner needs to be shown (consent renewal)
if ($consent->needsRenewal()) {
    // Show renewal banner
}

// Export for compliance records
$json = $consent->exportAsJson();
```

**Key Methods:**
- `setConsent(array $categories, bool $save)` - Set consent preferences
- `consentAll()` - Accept all non-essential cookies
- `rejectAll()` - Reject all non-essential cookies
- `hasConsented(string $category)` - Check consent for specific category
- `hasGivenConsent()` - Check if user has made a choice
- `needsRenewal()` - Check if consent needs renewal
- `getPreferences()` - Get current preferences
- `getAuditLog()` - Get compliance audit trail
- `exportAsJson()` - Export for compliance records

### Frontend (HTML/CSS/JavaScript)

**Files:** `banner.html`, `banner.css`, `banner.js`

The banner provides a user-friendly interface for managing cookie preferences.

#### HTML Structure
- Clean, semantic markup with ARIA labels for accessibility
- Four cookie categories: Essential, Analytics, Marketing, Preferences
- Three action buttons: Reject All, Accept All, Save Preferences
- Links to Privacy Policy and Cookie Policy

#### JavaScript (`banner.js`)
The `CookieConsentBanner` class manages all user interactions:

```javascript
// Auto-initialize
window.cookieConsentBanner = new CookieConsentBanner({
    autoShow: true,
    showDelay: 500,
    apiEndpoint: '/api/consent'
});

// Listen for preference changes
window.addEventListener('consentPreferencesChanged', function(event) {
    console.log('Preferences:', event.detail);
    // Conditionally load third-party scripts based on consent
});
```

#### CSS (`banner.css`)
- Modern, responsive design
- Mobile-first approach
- Smooth animations and transitions
- Accessible color contrast
- Works on all screen sizes

## Cookie Categories

| Category | Description |
|----------|-------------|
| **Essential** | Required for site functionality and security. Always enabled, cannot be disabled. |
| **Analytics** | Google Analytics, Mixpanel, and similar performance tracking tools. |
| **Marketing** | Facebook Pixel, Google Ads, and advertising networks for targeted ads. |
| **Preferences** | Remember user settings and personalization data across sessions. |

## Installation

### 1. Copy Files to Your Project

```bash
# Copy PHP consent handler
cp consent.php /path/to/your/project/

# Copy banner files
cp banner.html /path/to/your/project/public/
cp banner.css /path/to/your/project/public/
cp banner.js /path/to/your/project/public/
```

### 2. Include in Your Application

**PHP:**
```php
<?php
require_once 'consent.php';
$consent = new CookieConsent();
?>
```

**HTML:**
```html
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="banner.css">
</head>
<body>
    <!-- Your page content -->
    
    <!-- Include banner -->
    <?php include 'banner.html'; ?>
    
    <!-- Load JavaScript -->
    <script src="banner.js"></script>
</body>
</html>
```

## Usage Examples

### Basic Implementation

```php
<?php
require_once 'consent.php';

// Initialize consent handler
$consent = new CookieConsent();

// Check if banner should be shown
if (!$consent->hasGivenConsent() || $consent->needsRenewal()) {
    $showBanner = true;
}

// Conditionally load third-party scripts based on consent
if ($consent->hasConsented('analytics')) {
    // Load Google Analytics
    echo '<script async src="https://www.googletagmanager.com/gtag/js?id=GA_ID"></script>';
}
?>
```

### Handle AJAX Consent Submission

```php
<?php
// Handle POST request from banner.js
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['CONTENT_TYPE'] === 'application/json') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (is_array($input)) {
        $consent = new CookieConsent();
        $consent->setConsent($input, save: true);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Preferences saved']);
        exit;
    }
}
?>
```

### Get All Available Categories

```php
<?php
$categories = CookieConsent::getCategories();
foreach ($categories as $key => $description) {
    echo "$key: $description\n";
}
?>
```

### Export Consent for Compliance

```php
<?php
$consent = new CookieConsent();
$complianceRecord = $consent->exportAsJson();

// Save to compliance database
file_put_contents("consent_records/{$_SERVER['REMOTE_ADDR']}.json", $complianceRecord);
?>
```

### Listen to Consent Changes in JavaScript

```javascript
window.addEventListener('consentPreferencesChanged', function(event) {
    const { analytics, marketing, preferences } = event.detail;
    
    if (analytics) {
        console.log('User consented to analytics - load GA');
        // loadGoogleAnalytics();
    }
    
    if (marketing) {
        console.log('User consented to marketing - load ads');
        // loadFacebookPixel();
    }
    
    if (preferences) {
        console.log('User consented to preferences - enable personalization');
    }
});
```

## Configuration

### Constructor Options (PHP)

```php
$consent = new CookieConsent(
    $domain = '',           // Cookie domain (empty = current domain)
    $secure = true,         // Use secure flag (HTTPS only)
    $sameSite = 'Lax'       // SameSite attribute (Strict, Lax, None)
);
```

### Constructor Options (JavaScript)

```javascript
new CookieConsentBanner({
    autoShow: true,              // Auto-show banner on page load
    showDelay: 500,              // Delay before showing (ms)
    apiEndpoint: '/api/consent'  // Server endpoint for saving preferences
});
```

## Cookie Details

- **Name:** `uk_cookie_consent`
- **Expiration:** 34 days (covers 13-month ICO guideline)
- **Secure Flag:** Enabled by default (HTTPS only)
- **HttpOnly:** Enabled (not accessible via JavaScript)
- **SameSite:** Lax (default)
- **Storage:** JSON-encoded preferences object

## Compliance

This implementation helps meet requirements from:

- **GDPR** - Lawful basis for processing personal data
- **UK PECR** - Prior consent for non-essential cookies
- **ICO Guidance** - UK Information Commissioner's Office recommendations
- **EPRIVACY DIRECTIVE** - ePrivacy regulations in EU

### Audit Trail Information

Each consent record includes:
- Timestamp of consent
- User's IP address
- User agent (browser/device information)
- All preference selections
- Consent renewal status

## Security Considerations

✓ **HTTP-only cookies** - Cannot be accessed by JavaScript (XSS protection)  
✓ **Secure flag** - Only transmitted over HTTPS (MITM protection)  
✓ **SameSite attribute** - Protection against CSRF attacks  
✓ **Input validation** - Cookie categories validated server-side  
✓ **No external dependencies** - Reduced attack surface  

## Accessibility

The banner includes:
- Semantic HTML5 elements
- ARIA labels and descriptions
- Keyboard navigation support
- High contrast button states
- Screen reader friendly content
- Focus management

## Responsive Design

The banner is fully responsive:
- Desktop: Multi-column grid layout
- Tablet: Adjusted spacing and grid
- Mobile: Single column, full-width buttons

## Browser Support

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers (iOS Safari 14+, Chrome Android)

## Files Overview

```
.
├── consent.php          # Core PHP consent management class
├── banner.html          # HTML markup for consent banner
├── banner.css           # Styling for the banner
├── banner.js            # JavaScript for banner interactions
├── example.php          # Complete integration example
├── README.md            # This file
└── LICENSE              # MIT License
```

## Example Application

See `example.php` for a complete working example that demonstrates:
- Initializing the consent handler
- Showing/hiding the banner based on consent status
- Displaying personalized content based on preferences
- Exporting compliance records

Run it locally:
```bash
php -S localhost:8000
# Visit http://localhost:8000/example.php
```

## License

MIT License - See LICENSE file for details

## Support & Contributions

For issues, suggestions, or contributions, please open an issue or pull request on GitHub.

---

**Last Updated:** July 2026  
**Version:** 1.0.0
