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
use craft\helpers\App;
use consentpro\consentpro\ConsentPro;

/**
 * License validation service.
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
     * Check if current license is Pro tier.
     *
     * @return bool
     */
    public function isPro(): bool
    {
        $settings = ConsentPro::getInstance()->getSettings();

        if (empty($settings->licenseKey)) {
            return false;
        }

        // Check cached validation
        $cache = Craft::$app->getCache();
        $cacheKey = 'consentpro_license_' . md5($settings->licenseKey);
        $cached = $cache->get($cacheKey);

        if ($cached === false) {
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
     * Validate license key with remote API.
     *
     * @param string $key License key.
     * @return array Validation result.
     */
    public function validate(string $key): array
    {
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
                return [
                    'valid' => false,
                    'error' => Craft::t('consentpro', 'Invalid API response'),
                ];
            }

            // Cache the result
            if (!empty($data['valid'])) {
                $cache = Craft::$app->getCache();
                $cacheKey = 'consentpro_license_' . md5($key);
                $cache->set($cacheKey, $data, 86400 * 7); // 7 days
            }

            return $data;
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
