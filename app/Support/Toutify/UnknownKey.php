<?php

declare(strict_types=1);

namespace App\Support\Toutify;

/** No key in the JWKS matches the token; the caller refreshes the JWKS once and retries. */
final class UnknownKey extends \RuntimeException
{
}
