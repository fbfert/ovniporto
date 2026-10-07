<?php

namespace App\Domain\Manual;

use RuntimeException;

/** A manual chapter file does not have the expected shape. */
final class InvalidManualData extends RuntimeException {}
