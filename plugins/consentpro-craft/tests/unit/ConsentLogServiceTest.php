<?php
/**
 * ConsentLogService Unit Tests
 *
 * @link      https://consentpro.io
 * @copyright Copyright (c) ConsentPro Team
 */

namespace consentpro\consentpro\tests\unit;

use Codeception\Test\Unit;
use consentpro\consentpro\services\ConsentLogService;

/**
 * Unit tests for ConsentLogService.
 *
 * Tests the pure logic methods that don't require database access.
 */
class ConsentLogServiceTest extends Unit
{
    /**
     * @var ConsentLogService
     */
    private ConsentLogService $service;

    /**
     * Set up test fixtures.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ConsentLogService();
    }

    /**
     * Test determineConsentType returns accept_all when all categories accepted.
     */
    public function testDetermineConsentTypeAcceptAll(): void
    {
        $categories = [
            'essential' => true,
            'analytics' => true,
            'marketing' => true,
            'personalization' => true,
        ];

        $result = $this->service->determineConsentType($categories);

        $this->assertEquals('accept_all', $result);
    }

    /**
     * Test determineConsentType returns reject_non_essential when all non-essential rejected.
     */
    public function testDetermineConsentTypeRejectNonEssential(): void
    {
        $categories = [
            'essential' => true,
            'analytics' => false,
            'marketing' => false,
            'personalization' => false,
        ];

        $result = $this->service->determineConsentType($categories);

        $this->assertEquals('reject_non_essential', $result);
    }

    /**
     * Test determineConsentType returns custom for mixed categories.
     */
    public function testDetermineConsentTypeCustomMixed(): void
    {
        // Only analytics accepted
        $categories = [
            'essential' => true,
            'analytics' => true,
            'marketing' => false,
            'personalization' => false,
        ];

        $result = $this->service->determineConsentType($categories);

        $this->assertEquals('custom', $result);
    }

    /**
     * Test determineConsentType returns custom when some non-essential accepted.
     */
    public function testDetermineConsentTypeCustomPartial(): void
    {
        // Marketing and personalization accepted, analytics rejected
        $categories = [
            'essential' => true,
            'analytics' => false,
            'marketing' => true,
            'personalization' => true,
        ];

        $result = $this->service->determineConsentType($categories);

        $this->assertEquals('custom', $result);
    }

    /**
     * Test determineConsentType handles missing categories as rejected.
     */
    public function testDetermineConsentTypeMissingCategories(): void
    {
        // Only essential provided
        $categories = [
            'essential' => true,
        ];

        $result = $this->service->determineConsentType($categories);

        $this->assertEquals('reject_non_essential', $result);
    }

    /**
     * Test determineConsentType handles empty categories as reject.
     */
    public function testDetermineConsentTypeEmptyCategories(): void
    {
        $result = $this->service->determineConsentType([]);

        $this->assertEquals('reject_non_essential', $result);
    }
}
