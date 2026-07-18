<?php

/**
 * UK Cookie Consent Tracker
 * 
 * Handles GDPR and UK PECR compliance for cookie consent management.
 * Stores and retrieves user consent preferences securely.
 */
class CookieConsent
{
    /**
     * Cookie name for storing consent preferences
     */
    private const CONSENT_COOKIE_NAME = 'uk_cookie_consent';

    /**
     * Cookie expiration time (13 months as recommended by ICO)
     */
    private const CONSENT_COOKIE_LIFETIME = 34 * 24 * 60 * 60; // 34 days over 13 months

    /**
     * Allowed cookie categories
     */
    private const COOKIE_CATEGORIES = [
        'essential' => 'Essential cookies required for site functionality',
        'analytics' => 'Analytics and performance tracking',
        'marketing' => 'Marketing and advertising cookies',
        'preferences' => 'Preference and personalization cookies'
    ];

    /**
     * User consent preferences
     */
    private array $preferences = [];

    /**
     * Cookie domain
     */
    private string $domain = '';

    /**
     * Cookie path
     */
    private string $path = '/';

    /**
     * Whether to use secure flag for cookies
     */
    private bool $secure = true;

    /**
     * Whether to use SameSite attribute
     */
    private string $sameSite = 'Lax';

    /**
     * Constructor
     *
     * @param string $domain Cookie domain (leave empty for current domain)
     * @param bool $secure Use secure flag (HTTPS only)
     * @param string $sameSite SameSite attribute (Strict, Lax, None)
     */
    public function __construct(string $domain = '', bool $secure = true, string $sameSite = 'Lax')
    {
        $this->domain = $domain;
        $this->secure = $secure;
        $this->sameSite = $sameSite;
        $this->loadConsent();
    }

    /**
     * Load existing consent from cookie
     */
    private function loadConsent(): void
    {
        if (isset($_COOKIE[self::CONSENT_COOKIE_NAME])) {
            $consentData = json_decode($_COOKIE[self::CONSENT_COOKIE_NAME], true);
            if (is_array($consentData)) {
                $this->preferences = $consentData;
            }
        }
    }

    /**
     * Set consent for specific categories
     *
     * @param array $categories Array of category => bool pairs
     * @param bool $save Whether to save to cookie immediately
     * @return self
     */
    public function setConsent(array $categories, bool $save = true): self
    {
        // Essential cookies are always enabled
        $this->preferences['essential'] = true;

        // Set other categories
        foreach ($categories as $category => $consented) {
            if (array_key_exists($category, self::COOKIE_CATEGORIES)) {
                $this->preferences[$category] = (bool)$consented;
            }
        }

        // Add consent timestamp
        $this->preferences['consent_timestamp'] = time();

        if ($save) {
            $this->saveConsent();
        }

        return $this;
    }

    /**
     * Give consent to all non-essential categories
     *
     * @return self
     */
    public function consentAll(): self
    {
        $allCategories = array_fill_keys(array_keys(self::COOKIE_CATEGORIES), true);
        return $this->setConsent($allCategories);
    }

    /**
     * Reject all non-essential cookies
     *
     * @return self
     */
    public function rejectAll(): self
    {
        $allCategories = array_fill_keys(array_keys(self::COOKIE_CATEGORIES), false);
        $allCategories['essential'] = true; // Essential always on
        return $this->setConsent($allCategories);
    }

    /**
     * Check if user has consented to a specific category
     *
     * @param string $category Cookie category
     * @return bool
     */
    public function hasConsented(string $category): bool
    {
        return $this->preferences[$category] ?? false;
    }

    /**
     * Check if consent has been given for any category
     *
     * @return bool
     */
    public function hasGivenConsent(): bool
    {
        return isset($this->preferences['consent_timestamp']);
    }

    /**
     * Get all current preferences
     *
     * @return array
     */
    public function getPreferences(): array
    {
        return $this->preferences;
    }

    /**
     * Get consent timestamp
     *
     * @return int|null Unix timestamp or null if no consent given
     */
    public function getConsentTimestamp(): ?int
    {
        return $this->preferences['consent_timestamp'] ?? null;
    }

    /**
     * Check if consent needs renewal (over 13 months old)
     *
     * @return bool
     */
    public function needsRenewal(): bool
    {
        if (!$this->hasGivenConsent()) {
            return true;
        }

        $consentAge = time() - $this->getConsentTimestamp();
        $thirteenMonths = 13 * 30 * 24 * 60 * 60; // Approximate 13 months

        return $consentAge > $thirteenMonths;
    }

    /**
     * Save consent preferences to cookie
     *
     * @return bool
     */
    public function saveConsent(): bool
    {
        $consentJson = json_encode($this->preferences);

        return setcookie(
            self::CONSENT_COOKIE_NAME,
            $consentJson,
            [
                'expires' => time() + self::CONSENT_COOKIE_LIFETIME,
                'path' => $this->path,
                'domain' => $this->domain,
                'secure' => $this->secure,
                'httponly' => true,
                'samesite' => $this->sameSite
            ]
        );
    }

    /**
     * Clear all consent data
     *
     * @return bool
     */
    public function clearConsent(): bool
    {
        $this->preferences = [];
        return setcookie(
            self::CONSENT_COOKIE_NAME,
            '',
            [
                'expires' => time() - 3600,
                'path' => $this->path,
                'domain' => $this->domain,
                'secure' => $this->secure,
                'httponly' => true,
                'samesite' => $this->sameSite
            ]
        );
    }

    /**
     * Get all available cookie categories
     *
     * @return array
     */
    public static function getCategories(): array
    {
        return self::COOKIE_CATEGORIES;
    }

    /**
     * Generate consent audit log entry
     *
     * @return array
     */
    public function getAuditLog(): array
    {
        return [
            'timestamp' => $this->getConsentTimestamp(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'preferences' => $this->preferences,
            'renewal_needed' => $this->needsRenewal()
        ];
    }

    /**
     * Export consent as JSON (for compliance records)
     *
     * @return string
     */
    public function exportAsJson(): string
    {
        return json_encode([
            'audit_log' => $this->getAuditLog(),
            'preferences' => $this->preferences,
            'exported_at' => date('c')
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
