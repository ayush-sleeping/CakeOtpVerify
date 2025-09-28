<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\VerificationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\VerificationsTable Test Case
 */
class VerificationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\VerificationsTable
     */
    protected $Verifications;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Verifications',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Verifications') ? [] : ['className' => VerificationsTable::class];
        $this->Verifications = $this->getTableLocator()->get('Verifications', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Verifications);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\VerificationsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
