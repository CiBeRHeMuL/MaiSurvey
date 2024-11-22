<?php

namespace App\Presentation\Web\OpenApi\Attribute;

use App\Presentation\Web\Enum\HttpStatusCodeEnum;
use App\Presentation\Web\Response\Model as RM;
use Attribute;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Response;

#[Attribute]
class CriticalResponse extends Response
{
    public function __construct()
    {
        parent::__construct(
            response: 500,
            description: HttpStatusCodeEnum::InternalServerError->getName(),
            content: new JsonContent(
                ref: new Model(type: RM\Common\CriticalResponse::class),
            ),
        );
    }
}
