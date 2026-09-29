<?php

namespace App\Support;

use App\Enums\AccountType;

class AllowedEmail
{
    /**
     * @param  array<string, string>|null  $domains
     */
    public function __construct(private readonly ?array $domains = null) {}

    public function accountType(string $email): ?AccountType
    {
        $domain = $this->domain($email);

        if ($domain === null) {
            return null;
        }

        $type = $this->domains()[$domain] ?? AccountType::External->value;

        return AccountType::tryFrom($type) ?? AccountType::External;
    }

    public function allows(string $email): bool
    {
        return $this->accountType($email) !== null;
    }

    public function domain(string $email): ?string
    {
        $email = strtolower(trim($email));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $domain = substr(strrchr($email, '@') ?: '', 1);

        return $domain !== '' ? $domain : null;
    }

    /**
     * @return array<string, string>
     */
    private function domains(): array
    {
        if ($this->domains !== null) {
            return $this->domains;
        }

        $configured = config('sci.university_email_domains', []);

        return is_array($configured) ? $configured : [];
    }
}
