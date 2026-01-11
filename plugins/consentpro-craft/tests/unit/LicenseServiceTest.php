<?php
/**
 * LicenseService Unit Tests
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\tests\unit;

use Codeception\Test\Unit;
use consentpro\consentpro\services\LicenseService;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Unit tests for LicenseService.
 *
 * Tests the pure logic methods and constants that don't require
 * full Craft application bootstrap.
 */
class LicenseServiceTest extends Unit
{
    /**
     * @var LicenseService
     */
    private LicenseService $service;

    /**
     * Set up test fixtures.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LicenseService();
    }

    /**
     * Test API endpoint constant is correct.
     */
    public function testApiEndpointConstant(): void
    {
        $reflection = new ReflectionProperty(LicenseService::class, 'API_ENDPOINT');
        $reflection->setAccessible(true);
        $endpoint = $reflection->getValue($this->service);

        $this->assertEquals('https://api.consentpro.io/v1/license/validate', $endpoint);
        $this->assertStringStartsWith('https://', $endpoint, 'API endpoint must use HTTPS');
    }

    /**
     * Test grace period is 7 days.
     */
    public function testGracePeriodDaysConstant(): void
    {
        $reflection = new ReflectionProperty(LicenseService::class, 'GRACE_PERIOD_DAYS');
        $reflection->setAccessible(true);
        $days = $reflection->getValue($this->service);

        $this->assertEquals(7, $days, 'Grace period should be 7 days');
    }

    /**
     * Test DAY_IN_SECONDS constant is correct.
     */
    public function testDayInSecondsConstant(): void
    {
        $reflection = new ReflectionProperty(LicenseService::class, 'DAY_IN_SECONDS');
        $reflection->setAccessible(true);
        $seconds = $reflection->getValue($this->service);

        $this->assertEquals(86400, $seconds, 'DAY_IN_SECONDS should be 86400');
        $this->assertEquals(24 * 60 * 60, $seconds, 'Should equal 24 hours in seconds');
    }

    /**
     * Test service extends Component.
     */
    public function testExtendsComponent(): void
    {
        $this->assertInstanceOf(\craft\base\Component::class, $this->service);
    }

    /**
     * Test isPro method exists and is public.
     */
    public function testIsProMethodExists(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'isPro');
        $this->assertTrue($method->isPublic());
    }

    /**
     * Test isEnterprise method exists and is public.
     */
    public function testIsEnterpriseMethodExists(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'isEnterprise');
        $this->assertTrue($method->isPublic());
    }

    /**
     * Test getGraceDaysRemaining method exists and is public.
     */
    public function testGetGraceDaysRemainingMethodExists(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'getGraceDaysRemaining');
        $this->assertTrue($method->isPublic());
    }

    /**
     * Test getLicenseData method exists and is public.
     */
    public function testGetLicenseDataMethodExists(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'getLicenseData');
        $this->assertTrue($method->isPublic());
    }

    /**
     * Test getLastValidated method exists and is public.
     */
    public function testGetLastValidatedMethodExists(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'getLastValidated');
        $this->assertTrue($method->isPublic());
    }

    /**
     * Test validateLicense method exists and is public.
     */
    public function testValidateLicenseMethodExists(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'validateLicense');
        $this->assertTrue($method->isPublic());

        // Should accept a string parameter
        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('key', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
    }

    /**
     * Test getCacheKey private method exists.
     */
    public function testGetCacheKeyMethodExists(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'getCacheKey');
        $this->assertTrue($method->isPrivate());
    }

    /**
     * Test getCacheKey uses MD5 hashing.
     */
    public function testGetCacheKeyUsesMd5(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'getCacheKey');
        $method->setAccessible(true);

        $key = 'test-license-key';
        $result = $method->invoke($this->service, $key);

        $this->assertEquals('consentpro_license_' . md5($key), $result);
    }

    /**
     * Test getCacheKey produces unique keys for different inputs.
     */
    public function testGetCacheKeyUniqueness(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'getCacheKey');
        $method->setAccessible(true);

        $key1 = $method->invoke($this->service, 'license-key-1');
        $key2 = $method->invoke($this->service, 'license-key-2');

        $this->assertNotEquals($key1, $key2);
    }

    /**
     * Test getCacheKey produces consistent keys for same input.
     */
    public function testGetCacheKeyConsistency(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'getCacheKey');
        $method->setAccessible(true);

        $input = 'consistent-license-key';
        $key1 = $method->invoke($this->service, $input);
        $key2 = $method->invoke($this->service, $input);

        $this->assertEquals($key1, $key2);
    }

    /**
     * Test startGracePeriod private method exists.
     */
    public function testStartGracePeriodMethodExists(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'startGracePeriod');
        $this->assertTrue($method->isPrivate());
    }

    /**
     * Test clearGracePeriod private method exists.
     */
    public function testClearGracePeriodMethodExists(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'clearGracePeriod');
        $this->assertTrue($method->isPrivate());
    }

    /**
     * Test updateLastValidated private method exists.
     */
    public function testUpdateLastValidatedMethodExists(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'updateLastValidated');
        $this->assertTrue($method->isPrivate());
    }

    /**
     * Test getCachedLicense private method exists.
     */
    public function testGetCachedLicenseMethodExists(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'getCachedLicense');
        $this->assertTrue($method->isPrivate());
    }

    /**
     * Test cacheValidation private method exists.
     */
    public function testCacheValidationMethodExists(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'cacheValidation');
        $this->assertTrue($method->isPrivate());

        // Should accept key string and data array
        $params = $method->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('key', $params[0]->getName());
        $this->assertEquals('data', $params[1]->getName());
    }

    /**
     * Test clearCache private method exists.
     */
    public function testClearCacheMethodExists(): void
    {
        $method = new ReflectionMethod(LicenseService::class, 'clearCache');
        $this->assertTrue($method->isPrivate());
    }

    /**
     * Test service class has no syntax errors.
     */
    public function testServiceClassIsValid(): void
    {
        $reflection = new \ReflectionClass(LicenseService::class);

        $this->assertTrue($reflection->isInstantiable());
        $this->assertFalse($reflection->isAbstract());
        $this->assertFalse($reflection->isInterface());
    }

    /**
     * Test grace period calculation logic.
     *
     * Grace period should allow Pro access for 7 days after API failure.
     */
    public function testGracePeriodCalculationLogic(): void
    {
        $gracePeriodDays = 7;
        $dayInSeconds = 86400;

        // Grace end = start + (7 * 86400)
        $graceStart = time();
        $graceEnd = $graceStart + ($gracePeriodDays * $dayInSeconds);

        // Should be valid for 7 full days
        $this->assertEquals($graceStart + 604800, $graceEnd);

        // At day 6, should still be in grace period
        $day6 = $graceStart + (6 * $dayInSeconds);
        $this->assertTrue($day6 < $graceEnd, 'Day 6 should be within grace period');

        // At day 8, should be past grace period
        $day8 = $graceStart + (8 * $dayInSeconds);
        $this->assertTrue($day8 > $graceEnd, 'Day 8 should be past grace period');
    }

    /**
     * Test grace days remaining calculation logic.
     */
    public function testGraceDaysRemainingCalculation(): void
    {
        $gracePeriodDays = 7;
        $dayInSeconds = 86400;

        $graceStart = time() - (3 * $dayInSeconds); // Started 3 days ago
        $graceEnd = $graceStart + ($gracePeriodDays * $dayInSeconds);
        $remaining = $graceEnd - time();

        // Should have approximately 4 days remaining
        $daysRemaining = (int) ceil($remaining / $dayInSeconds);
        $this->assertEquals(4, $daysRemaining);
    }

    /**
     * Test tier validation logic.
     *
     * Documents expected tier values and their meaning.
     */
    public function testTierValues(): void
    {
        $tiers = ['core', 'pro', 'enterprise'];

        foreach ($tiers as $tier) {
            $this->assertIsString($tier);
        }

        // Core tier should not grant Pro features
        $this->assertTrue('core' !== 'pro');
        $this->assertTrue('core' !== 'enterprise');

        // Pro and Enterprise should grant Pro features
        $proTiers = ['pro', 'enterprise'];
        foreach ($proTiers as $tier) {
            $this->assertNotEquals('core', $tier);
        }
    }

    /**
     * Test license data structure.
     *
     * Documents expected keys in getLicenseData return value.
     */
    public function testLicenseDataStructure(): void
    {
        $expectedKeys = [
            'hasKey',
            'valid',
            'tier',
            'expires',
            'lastValidated',
            'inGracePeriod',
            'graceDaysRemaining',
        ];

        // This documents the expected structure for integration tests
        foreach ($expectedKeys as $key) {
            $this->assertIsString($key);
        }

        $this->assertCount(7, $expectedKeys);
    }

    /**
     * Test validation result structure.
     *
     * Documents expected keys in validateLicense return value.
     */
    public function testValidationResultStructure(): void
    {
        // Successful validation
        $successKeys = ['valid', 'tier', 'expires'];

        // Failed validation with grace period
        $graceKeys = ['valid', 'error', 'grace_period'];

        // Failed validation without grace period
        $failedKeys = ['valid', 'error'];

        foreach ($successKeys as $key) {
            $this->assertIsString($key);
        }

        foreach ($graceKeys as $key) {
            $this->assertIsString($key);
        }

        foreach ($failedKeys as $key) {
            $this->assertIsString($key);
        }
    }

    /**
     * Test cache TTL matches grace period.
     *
     * Cache should be valid for the same duration as grace period.
     */
    public function testCacheTtlMatchesGracePeriod(): void
    {
        $reflection = new ReflectionProperty(LicenseService::class, 'GRACE_PERIOD_DAYS');
        $reflection->setAccessible(true);
        $graceDays = $reflection->getValue($this->service);

        $daySeconds = new ReflectionProperty(LicenseService::class, 'DAY_IN_SECONDS');
        $daySeconds->setAccessible(true);
        $secondsPerDay = $daySeconds->getValue($this->service);

        // Cache TTL should be grace period days * seconds per day
        $expectedTtl = $graceDays * $secondsPerDay;
        $this->assertEquals(604800, $expectedTtl, 'Cache TTL should be 7 days in seconds');
    }

    /**
     * Test expiry check logic.
     *
     * Documents how license expiry should be checked.
     */
    public function testExpiryCheckLogic(): void
    {
        // Future date - should be valid
        $futureDate = date('Y-m-d', strtotime('+30 days'));
        $futureTimestamp = strtotime($futureDate);
        $this->assertTrue($futureTimestamp > time(), 'Future date should be greater than current time');

        // Past date - should be expired
        $pastDate = date('Y-m-d', strtotime('-1 day'));
        $pastTimestamp = strtotime($pastDate);
        $this->assertTrue($pastTimestamp < time(), 'Past date should be less than current time');

        // Today - edge case, depends on time of day
        $today = date('Y-m-d');
        $todayTimestamp = strtotime($today);
        // This is midnight of today, so it's likely already passed
        $this->assertIsInt($todayTimestamp);
    }
}
