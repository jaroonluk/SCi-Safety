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

    /*
    | Temporary testing aid: let Super Admin act as another user.
    | Keep this false outside the system-test window.
    */
    'impersonation_enabled' => (bool) env('IMPERSONATION_ENABLED', false),

    /*
    | Friendly labels for building.rep_build codes shown on the request form.
    */
    'building_labels' => [
        'SC.01' => 'อาคาร SC.01',
        'SC.02' => 'อาคาร SC.02',
        'SC.03' => 'อาคาร SC.03',
        'SC.04' => 'อาคาร SC.04',
        'SC.05' => 'อาคาร SC.05',
        'SC.06' => 'อาคาร SC.06',
        'SC.07' => 'อาคาร SC.07',
        'SC.08' => 'อาคาร SC.08',
        'วิทยวิภาส' => 'อาคารวิทยวิภาส',
    ],

];
