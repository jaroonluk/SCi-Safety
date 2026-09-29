<?php

return [

    /*
    | University accounts are classified by exact domain.
    | Any other valid email address is an external requester,
    | provided Google can authenticate it.
    */
    'university_email_domains' => [
        'kkumail.com' => 'student',
        'kku.ac.th' => 'staff',
    ],

    'super_admin_emails' => array_values(array_filter(array_map(
        static fn (string $email): string => strtolower(trim($email)),
        explode(',', (string) env('SUPER_ADMIN_EMAILS', ''))
    ))),

];
