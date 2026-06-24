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
            'doc_no'  => $docNo,
            'company' => 'SKY',
            'status'  => 'Open',
        ]);
    }

    public function testFirstNumberPerCompanyMonth(): void
    {
        $m = new TransferRequestModel();
        $this->assertSame('ITRS26060001', $m->nextDocNo('SKY', '2606'));
        $this->assertSame('ITRJ26060001', $m->nextDocNo('JOJO', '2606'));
    }

    public function testRunningIncrementsWithinCompany(): void
    {
        $this->seed('ITRS26060001');
        $this->seed('ITRS26060002');

        $m = new TransferRequestModel();
        $this->assertSame('ITRS26060003', $m->nextDocNo('SKY', '2606'));
    }

    public function testCompaniesHaveSeparateSequences(): void
    {
        $this->seed('ITRS26060001');
        $this->seed('ITRS26060002');

        $m = new TransferRequestModel();
        // JOJO is untouched by SKY documents.
        $this->assertSame('ITRJ26060001', $m->nextDocNo('JOJO', '2606'));
    }

    public function testMonthsHaveSeparateSequences(): void
    {
        $this->seed('ITRS26060001');

        $m = new TransferRequestModel();
        // A different month restarts at 1.
        $this->assertSame('ITRS26050001', $m->nextDocNo('SKY', '2605'));
    }
}
