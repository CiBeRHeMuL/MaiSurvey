<?php

namespace App\Presentation\Web\OpenApi\Attribute;

use App\Presentation\Web\Response\Model as RM;
use Attribute;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class SuccessResponse extends Response
{
    public function __construct(
        string|null $dataModel = null,
    ) {
        $isTypedData = !in_array(
            $dataModel,
            [
                'boolean',
                'integer',
                'number',
                'string',
                'array',
                'object',
            ],
        );
        parent::__construct(
            response: 200,
            description: 'OK',
            content: $dataModel === null
                ? new JsonContent(
                    ref: new Model(type: RM\Common\SuccessResponse::class),
                )
                : new JsonContent(
                    allOf: [
                        new Schema(ref: new Model(type: RM\Common\SuccessResponse::class)),
                        new Schema(
                            properties: [
                                new Property(
                                    'data',
                                    ref: $isTypedData ? new Model(type: $dataModel) : null,
                                    type: $isTypedData ? null : $dataModel,
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
        );
    }
}
