<?php

namespace Tests\Unit;

use App\Support\BranchName;
use PHPUnit\Framework\TestCase;

class BranchNameTest extends TestCase
{
    public function test_it_removes_a_trailing_branch_suffix_and_formats_the_name(): void
    {
        $this->assertSame('Carmona', BranchName::format('CARMONA BRANCH'));
        $this->assertSame('Biñan', BranchName::format('biñan branch '));
    }

    public function test_it_preserves_branch_names_without_the_suffix(): void
    {
        $this->assertSame('Santa Rosa', BranchName::format('sAnTa RoSa'));
    }

    public function test_it_uses_the_supplied_fallback_for_blank_names(): void
    {
        $this->assertSame('Assigned Branch', BranchName::format('  ', 'Assigned Branch'));
    }
}
