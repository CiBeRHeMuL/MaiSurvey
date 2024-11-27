<?php

namespace App\Presentation\Web\OpenApi\Attribute;

use Attribute;
use OpenApi\Attributes\MediaType;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class FileResponse extends Response
{
    public function __construct(string $contentType)
    {
        parent::__construct(
            response: 200,
            description: 'Ok',
            content: new MediaType(
                $contentType,
                schema: new Schema(
                    type: 'string',
                    format: 'binary',
                ),
            ),
        );
    }
}
