<?php
/*
 * Copyright 2024 Code Inc. <https://www.codeinc.co>
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace CodeInc\Office2PdfClient;

use Exception as BaseException;

class Exception extends BaseException
{
    public const int ERROR_LOCAL_FILE = 100;
    public const int ERROR_REQUEST = 200;
    public const int ERROR_RESPONSE = 300;
    public const int ERROR_UNSUPPORTED_FILE_TYPE = 400;
}