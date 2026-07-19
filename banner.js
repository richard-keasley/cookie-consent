/**
 * Cookie Consent Banner Manager
 * Handles user interactions with the cookie consent banner
 */

class CookieConsentBanner {
    constructor(options = {}) {
        this.banner = document.getElementById('cookie-consent-banner');
        this.closeBtn = document.getElementById('cookie-banner-close');
        this.rejectAllBtn = document.getElementById('cookie-reject-all');
        this.acceptAllBtn = document.getElementById('cookie-accept-all');
        this.savePreferencesBtn = document.getElementById('cookie-save-preferences');
        
        this.checkboxes = {
            essential: document.querySelector('input[name="cookie-essential"]'),
            analytics: document.querySelector('input[name="cookie-analytics"]'),
            marketing: document.querySelector('input[name="cookie-marketing"]'),
            preferences: document.querySelector('input[name="cookie-preferences"]')
        };

        this.options = {
            apiEndpoint: options.apiEndpoint || '/api/consent',
            autoShow: options.autoShow !== false,
            showDelay: options.showDelay || 500,
            ...options
        };

        if (this.banner) {
            this.init();
        }
    }

    /**
     * Initialize banner event listeners
     */
    init() {
        this.closeBtn?.addEventListener('click', () => this.closeBanner());
        this.rejectAllBtn?.addEventListener('click', () => this.rejectAll());
        this.acceptAllBtn?.addEventListener('click', () => this.acceptAll());
        this.savePreferencesBtn?.addEventListener('click', () => this.savePreferences());

        // Close banner when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.banner.contains(e.target) && this.banner.classList.contains('show')) {
                // Don't close if clicking on interactive elements
                if (!e.target.closest('a, button')) {
                    this.closeBanner();
                }
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.banner.classList.contains('show')) {
                this.closeBanner();
            }
        });

        if (this.options.autoShow) {
            setTimeout(() => this.showBanner(), this.options.showDelay);
        }
    }

    /**
     * Show the banner
     */
    showBanner() {
        if (this.banner && !this.banner.classList.contains('show')) {
            this.banner.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * Close the banner
     */
    closeBanner() {
        if (this.banner) {
            this.banner.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    /**
     * Accept all cookies
     */
    acceptAll() {
        this.setAllCheckboxes(true);
        this.savePreferences();
    }

    /**
     * Reject all non-essential cookies
     */
    rejectAll() {
        this.checkboxes.analytics.checked = false;
        this.checkboxes.marketing.checked = false;
        this.checkboxes.preferences.checked = false;
        this.savePreferences();
    }

    /**
     * Set all checkboxes to a specific state
     */
    setAllCheckboxes(state) {
        Object.keys(this.checkboxes).forEach(key => {
            if (key !== 'essential') {
                this.checkboxes[key].checked = state;
            }
        });
    }

    /**
     * Save current preferences
     */
    savePreferences() {
        const preferences = {
            essential: true,
            analytics: this.checkboxes.analytics.checked,
            marketing: this.checkboxes.marketing.checked,
            preferences: this.checkboxes.preferences.checked
        };

        // Send to server via AJAX if endpoint is configured
        if (this.options.apiEndpoint) {
            this.sendToServer(preferences);
        }

        // Dispatch custom event for application to listen to
        window.dispatchEvent(new CustomEvent('consentPreferencesChanged', {
            detail: preferences
        }));

        // Close banner after saving
        setTimeout(() => this.closeBanner(), 300);
    }

    /**
     * Send preferences to server
     */
    sendToServer(preferences) {
        fetch(this.options.apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(preferences)
        })
        .catch(error => {
            console.error('Error sending consent preferences:', error);
        });
    }

    /**
     * Get current preferences
     */
    getPreferences() {
        return {
            essential: true,
            analytics: this.checkboxes.analytics.checked,
            marketing: this.checkboxes.marketing.checked,
            preferences: this.checkboxes.preferences.checked
        };
    }

    /**
     * Reset banner to default state
     */
    reset() {
        this.setAllCheckboxes(false);
        this.banner.classList.remove('show');
    }
}

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.cookieConsentBanner = new CookieConsentBanner({
        autoShow: true,
        showDelay: 500
    });
});
