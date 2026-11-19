<?php
return [
    'free' => ['name' => 'Free', 'price' => 0, 'projects' => 2, 'members' => 3, 'storage_mb' => 50,
        'features' => ['2 projects', '3 members', '50 MB storage', 'Team chat']],
    'pro' => ['name' => 'Pro', 'price' => 29, 'projects' => 20, 'members' => 25, 'storage_mb' => 5000,
        'features' => ['20 projects', '25 members', '5 GB storage', 'Data exports', 'Audit logs']],
    'enterprise' => ['name' => 'Enterprise', 'price' => 99, 'projects' => null, 'members' => null, 'storage_mb' => 100000,
        'features' => ['Unlimited projects', 'Unlimited members', '100 GB storage', 'Data exports', 'Audit logs', 'Priority support']],
];
