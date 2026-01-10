<?php
/**
 * License Service
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\services;

use Craft;
use craft\base\Component;
use consentpro\consentpro\ConsentPro;

/**
 * License validation service.
 *
 * Handles license key validation against remote API with grace period
 * support for API failures. Caches validation results and stores
 * license metadata in plugin settings.
 */
class LicenseService extends Component
{
    /**
     * License API endpoint.
     */
    private const API_ENDPOINT = 'https://api.consentpro.io/v1/license/validate';

    /**
     * Grace period in days.
     */
    private const GRACE_PERIOD_DAYS = 7;

    /**
     * Seconds in a day.
     */
    private const DAY_IN_SECONDS = 86400;

    /**
     * Check if current license is Pro tier.
     *
     * Includes grace period logic: if API validation failed recently
     * but we had a valid license before, allow grace period access.
     *
     * @return bool
     */
    public function isPro(): bool
    {
        $settings = ConsentPro::getInstance()->getSettings();

        // Check grace period first (API failed but was previously valid)
        if (!empty($settings->licenseGracePeriodStart) && $settings->licenseWasValid) {
            $graceEnd = $settings->licenseGracePeriodStart + (self::GRACE_PERIOD_DAYS * self::DAY_IN_SECONDS);
            if (time() < $graceEnd) {
                return true;
            }
        }

        if (empty($settings->licenseKey)) {
            return false;
        }

        // Check cached validation
        $cached = $this->getCachedLicense();

        if ($cached === null) {
            return false;
        }

        if (empty($cached['valid']) || empty($cached['tier']) || $cached['tier'] === 'core') {
            return false;
        }

        // Check expiry
        if (!empty($cached['expires'])) {
            $expires = strtotime($cached['expires']);
            if ($expires && time() > $expires) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if current license is Enterprise tier.
     *
     * @return bool
     */
    public function isEnterprise(): bool
    {
        $cached = $this->getCachedLicense();
        return !empty($cached['tier']) && $cached['tier'] === 'enterprise';
    }

    /**
     * Get remaining grace period days.
     *
     * @return int|null Days remaining, or null if not in grace period.
     */
    public function getGraceDaysRemaining(): ?int
    {
        $settings = ConsentPro::getInstance()->getSettings();

        if (empty($settings->licenseGracePeriodStart)) {
            return null;
        }

        $graceEnd = $settings->licenseGracePeriodStart + (self::GRACE_PERIOD_DAYS * self::DAY_IN_SECONDS);
        $remaining = $graceEnd - time();

        if ($remaining <= 0) {
            return null;
        }

        return (int) ceil($remaining / self::DAY_IN_SECONDS);
    }

    /**
     * Get license data for UI display.
     *
     * @return array License data including tier, expiry, and validation info.
     */
    public function getLicenseData(): array
    {
        $settings = ConsentPro::getInstance()->getSettings();
        $cached = $this->getCachedLicense();

        return [
            'hasKey' => !empty($settings->licenseKey),
            'valid' => $cached['valid'] ?? false,
            'tier' => $cached['tier'] ?? 'core',
            'expires' => $cached['expires'] ?? null,
            'lastValidated' => $settings->licenseLastValidated,
            'inGracePeriod' => $this->getGraceDaysRemaining() !== null,
            'graceDaysRemaining' => $this->getGraceDaysRemaining(),
        ];
    }

    /**
     * Get last validation timestamp.
     *
     * @return int|null Unix timestamp or null if never validated.
     */
    public function getLastValidated(): ?int
    {
        return ConsentPro::getInstance()->getSettings()->licenseLastValidated;
    }

    /**
     * Validate license key with remote API.
     *
     * Handles grace period: if API fails but we had a valid license,
     * start grace period countdown instead of immediately invalidating.
     *
     * @param string $key License key.
     * @return array Validation result with 'valid', 'tier', 'expires', 'error', and 'grace_period' keys.
     */
    public function validateLicense(string $key): array
    {
        $settings = ConsentPro::getInstance()->getSettings();
        $wasValid = $this->isPro();

        $client = Craft::createGuzzleClient();

        try {
            $response = $client->post(self::API_ENDPOINT, [
                'json' => [
                    'key' => $key,
                    'domain' => Craft::$app->getSites()->getPrimarySite()->getBaseUrl(),
                    'version' => ConsentPro::getInstance()->getVersion(),
                ],
                'timeout' => 15,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!is_array($data)) {
                // Invalid response - enter grace period if previously valid
                if ($wasValid) {
                    $this->startGracePeriod();
                    return [
                        'valid' => false,
                        'error' => Craft::t('consentpro', 'Invalid API response'),
                        'grace_period' => true,
                    ];
                }
                return [
                    'valid' => false,
                    'error' => Craft::t('consentpro', 'Invalid API response'),
                ];
            }

            // Cache the result and update settings
            if (!empty($data['valid'])) {
                $this->cacheValidation($key, $data);
                $this->clearGracePeriod();
                $this->updateLastValidated();
            } else {
                // License invalid - clear cache
                $this->clearCache($key);
            }

            return $data;
        } catch (\Exception $e) {
            // API failure - enter grace period if previously valid
            if ($wasValid) {
                $this->startGracePeriod();
                return [
                    'valid' => false,
                    'error' => $e->getMessage(),
                    'grace_period' => true,
                ];
            }
            return [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get cached license data.
     *
     * @return array|null Cached data or null if not cached.
     */
    private function getCachedLicense(): ?array
    {
        $settings = ConsentPro::getInstance()->getSettings();

        if (empty($settings->licenseKey)) {
            return null;
        }

        $cache = Craft::$app->getCache();
        $cacheKey = $this->getCacheKey($settings->licenseKey);
        $cached = $cache->get($cacheKey);

        return $cached === false ? null : $cached;
    }

    /**
     * Cache validation result.
     *
     * @param string $key License key.
     * @param array $data Validation data.
     */
    private function cacheValidation(string $key, array $data): void
    {
        $cache = Craft::$app->getCache();
        $cacheKey = $this->getCacheKey($key);
        $cache->set($cacheKey, $data, self::GRACE_PERIOD_DAYS * self::DAY_IN_SECONDS);
    }

    /**
     * Clear cached validation.
     *
     * @param string $key License key.
     */
    private function clearCache(string $key): void
    {
        $cache = Craft::$app->getCache();
        $cacheKey = $this->getCacheKey($key);
        $cache->delete($cacheKey);
    }

    /**
     * Get cache key for license.
     *
     * @param string $key License key.
     * @return string Cache key.
     */
    private function getCacheKey(string $key): string
    {
        return 'consentpro_license_' . md5($key);
    }

    /**
     * Start grace period for license validation.
     */
    private function startGracePeriod(): void
    {
        $plugin = ConsentPro::getInstance();
        $settings = $plugin->getSettings();

        // Only start grace period if not already in one
        if (empty($settings->licenseGracePeriodStart)) {
            $settings->licenseGracePeriodStart = time();
            $settings->licenseWasValid = true;
            Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->toArray());
        }
    }

    /**
     * Clear grace period (license validated successfully).
     */
    private function clearGracePeriod(): void
    {
        $plugin = ConsentPro::getInstance();
        $settings = $plugin->getSettings();

        if (!empty($settings->licenseGracePeriodStart) || $settings->licenseWasValid) {
            $settings->licenseGracePeriodStart = null;
            $settings->licenseWasValid = false;
            Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->toArray());
        }
    }

    /**
     * Update last validated timestamp.
     */
    private function updateLastValidated(): void
    {
        $plugin = ConsentPro::getInstance();
        $settings = $plugin->getSettings();
        $settings->licenseLastValidated = time();
        Craft::$app->getPlugins()->savePluginSettings($plugin, $settings->toArray());
    }
}
