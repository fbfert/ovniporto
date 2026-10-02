<?php

use Illuminate\Support\Facades\Schedule;

// Temporary report photos no report claimed are deleted after 24 h.
Schedule::command('sightings:prune-uploads')->hourly();
