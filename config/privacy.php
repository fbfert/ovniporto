<?php

return [
    // Paid orders of a deleted account are kept anonymized for tax law, then purged.
    // Term confirmed by the encarregado on 2026-10-16 (CTN art. 173/174: 5 years after payment).
    'fiscal_retention_years' => (int) env('PRIVACY_FISCAL_RETENTION_YEARS', 5),

    // Web server access logs: docker/nginx/logrotate.conf keeps 26 weekly files (6 months,
    // Marco Civil art. 15), confirmed by the encarregado on 2026-10-16.
    'access_log_months' => 6,
];
