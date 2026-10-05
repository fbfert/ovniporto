<?php

namespace App\Domain\Origin;

use RuntimeException;

/** The versioned origin data files do not have the expected shape. */
final class InvalidOriginData extends RuntimeException {}
