<?php

namespace App\Presentation\Web\Response\Model;

use OpenApi\Attributes as OA;

readonly class CreatedUsersInfo
{
    public function __construct(
        public int $created,
        /** Адрес, по которому можно получить список созданных пользователей */
        #[OA\Property(format: 'url')]
        public string $fetch_url,
        /** Адрес, по которому можно скачать выгрузку по созданным пользователям */
        #[OA\Property(format: 'url')]
        public string $export_url,
    ) {
    }
}
