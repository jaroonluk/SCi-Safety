<?php

namespace Tests\Unit;

use App\Enums\AccountType;
use App\Support\AllowedEmail;
use PHPUnit\Framework\TestCase;

class AllowedEmailTest extends TestCase
{
    private AllowedEmail $allowedEmail;

    protected function setUp(): void
    {
        parent::setUp();

        $this->allowedEmail = new AllowedEmail([
            'kkumail.com' => 'student',
            'kku.ac.th' => 'staff',
        ]);
    }

    public function test_student_mail_is_classified_as_a_student(): void
    {
        $this->assertSame(AccountType::Student, $this->allowedEmail->accountType('student.name@KKUMAIL.COM'));
    }

    public function test_personnel_mail_is_classified_as_staff(): void
    {
        $this->assertSame(AccountType::Staff, $this->allowedEmail->accountType('officer@kku.ac.th'));
    }

    public function test_any_other_valid_google_mail_is_external(): void
    {
        $this->assertSame(AccountType::External, $this->allowedEmail->accountType('visitor@gmail.com'));
        $this->assertSame(AccountType::External, $this->allowedEmail->accountType('person@hotmail.com'));
        $this->assertTrue($this->allowedEmail->allows('contact@company.co.th'));
    }

    public function test_invalid_addresses_are_rejected(): void
    {
        $this->assertNull($this->allowedEmail->accountType('not-an-email'));
        $this->assertFalse($this->allowedEmail->allows(''));
    }
}
