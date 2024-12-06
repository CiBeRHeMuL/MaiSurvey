<?php

namespace App\Presentation\Web\Response\Model\SurveyItemData;

use App\Domain\Enum\SurveyItemTypeEnum;

interface ItemDataInterface
{
    /**
     * @return value-of<SurveyItemTypeEnum>
     */
    public function getType(): string;
}
