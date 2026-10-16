<?php

it('keeps the retention terms the encarregado confirmed by default', function () {
    $config = require dirname(__DIR__, 3).'/config/privacy.php';

    expect($config['fiscal_retention_years'])->toBe(5)
        ->and($config['inactive_account_years'])->toBe(5)
        ->and($config['inactivity_notice_days'])->toBe(30)
        ->and($config['access_log_months'])->toBe(6);
});
