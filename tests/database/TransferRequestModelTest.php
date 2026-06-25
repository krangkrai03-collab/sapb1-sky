<?php

namespace Tests\Database;

use App\Models\TransferRequestModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class TransferRequestModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = null;

    private function seed(string $docNo): void
    {
        (new TransferRequestModel())->insert([
            'doc_no' => $docNo,
            'status' => 'Open',
        ]);
    }

    public function testFirstNumberOfMonth(): void
    {
        $m = new TransferRequestModel();
        $this->assertSame('ITR26060001', $m->nextDocNo('2606'));
    }

    public function testRunningIncrements(): void
    {
        $this->seed('ITR26060001');
        $this->seed('ITR26060002');

        $m = new TransferRequestModel();
        $this->assertSame('ITR26060003', $m->nextDocNo('2606'));
    }

    public function testMonthsHaveSeparateSequences(): void
    {
        $this->seed('ITR26060001');

        $m = new TransferRequestModel();
        // A different month restarts at 1.
        $this->assertSame('ITR26050001', $m->nextDocNo('2605'));
    }
}
