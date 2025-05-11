<?php

namespace App\Presentation\Web\OpenApi\Attribute;

use App\Presentation\Web\Response\Model\Catalog\EnumCase;
use Attribute;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;
use UnitEnum;

#[Attribute(
    Attribute::TARGET_METHOD
    | Attribute::TARGET_PROPERTY
    | Attribute::TARGET_PARAMETER
    | Attribute::TARGET_CLASS_CONSTANT
    | Attribute::IS_REPEATABLE
)]
class EnumCases extends Property
{
    /**
     * @param class-string<UnitEnum> $enumClass
     */
    public function __construct(
        string $enumClass,
    ) {
        parent::__construct(
            type: 'array',
            items: new Items(
                allOf: [
                    new Schema(ref: new Model(type: EnumCase::class)),
                    new Schema(
                        properties: [new Property(property: 'value', ref: new Model(type: $enumClass))],
                        type: 'object',
                    ),
                ],
            ),
        );
    }
}
