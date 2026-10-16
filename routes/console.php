<?php

use Illuminate\Support\Facades\Schedule;

// Temporary report photos no report claimed are deleted after 24 h.
Schedule::command('sightings:prune-uploads')->hourly();

// Orders still waiting for payment after 2 h are canceled (the stock was never taken).
Schedule::command('orders:cancel-abandoned')->everyTenMinutes();

// Delivery status from the shipping provider, once a day.
Schedule::command('orders:track-shipments')->dailyAt('07:00');

// sitemap.xml follows what was published (served as a ready file between runs).
Schedule::command('sitemap:generate')->hourly();

// Accounts without access for 5 years: notice 30 days before, then anonymized (panel accounts alert the admins).
Schedule::command('members:anonymize-inactive')->dailyAt('03:15');

// Paid orders are anonymized once the tax retention ends; those of deleted accounts go for good.
Schedule::command('privacy:purge-orders')->dailyAt('03:30');
