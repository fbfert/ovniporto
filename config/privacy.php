<?php

return [
    // Paid orders of a deleted account are kept anonymized for tax law, then purged.
    // The encarregado confirms the term (CTN art. 173/174: 5 years).
    'fiscal_retention_years' => (int) env('PRIVACY_FISCAL_RETENTION_YEARS', 5),

    // Web server access logs: docker/nginx/logrotate.conf keeps 26 weekly files (6 months).
    'access_log_months' => 6,
];
