<?php

namespace App\Presentation\Web\OpenApi\Attribute;

use App\Presentation\Web\Response\Model as RM;
use Attribute;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Response;
use OpenApi\Attributes\Schema;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class SuccessPaginationResponse extends Response
{
    public function __construct(
        string|null $dataModel,
    ) {
        parent::__construct(
            response: 200,
            description: 'OK',
            content: new JsonContent(
                allOf: [
                    new Schema(ref: new Model(type: RM\Common\SuccessWithPaginationResponse::class)),
                    new Schema(
                        properties: [
                            new Property(
                                'data',
                                properties: [
                                    new Property(
                                        'items',
                                        type: 'array',
                                        items: new Items(
                                            ref: new Model(type: $dataModel),
                                        ),
                                    ),
                                ],
                                type: 'object',
                            ),
                        ],
                        type: 'object',
                    ),
                ],
            ),
        );
    }
}
