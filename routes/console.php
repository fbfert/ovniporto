<?php

use Illuminate\Support\Facades\Schedule;

// Temporary report photos no report claimed are deleted after 24 h.
Schedule::command('sightings:prune-uploads')->hourly();

// Orders still waiting for payment after 2 h are canceled (the stock was never taken).
Schedule::command('orders:cancel-abandoned')->everyTenMinutes();

// Delivery status from the shipping provider, once a day.
Schedule::command('orders:track-shipments')->dailyAt('07:00');
