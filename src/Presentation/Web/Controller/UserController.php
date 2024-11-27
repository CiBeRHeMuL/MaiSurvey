<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\User\CreateFullUserDto;
use App\Application\Dto\User\GetAllDto;
use App\Application\UseCase\User\CreateUserUseCase;
use App\Application\UseCase\User\GetAllUseCase;
use App\Domain\Enum\PermissionEnum;
use App\Presentation\Web\Enum\ErrorSlugEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\Error;
use App\Presentation\Web\Response\Model\Common\ErrorResponse;
use App\Presentation\Web\Response\Model\Common\PaginatedData;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\Common\SuccessWithPaginationResponse;
use App\Presentation\Web\Response\Model\User;
use App\Presentation\Web\Response\Response;
use App\Presentation\Web\Service\DataExport\FileDataExportFactoryInterface;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

class UserController extends BaseController
{
    /** Создание пользователя */
    #[Route('/user', 'create-full-user', methods: ['POST'])]
    #[IsGranted(PermissionEnum::UserCreate->value, statusCode: 404)]
    #[OA\Tag('user')]
    #[LOA\SuccessResponse(User::class)]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function create(
        #[MapRequestPayload]
        CreateFullUserDto $dto,
        CreateUserUseCase $useCase,
        LoggerInterface $logger,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $user = $useCase->execute($dto);
        return Response::success(
            new SuccessResponse(
                User::fromUser($user),
            ),
        );
    }

    /** Получить всех пользователей с пагинацией и сортировкой */
    #[Route('/users', 'get-all-users', methods: ['GET'])]
    #[IsGranted(PermissionEnum::UserView->value, statusCode: 404)]
    #[OA\Tag('user')]
    #[LOA\SuccessPaginationResponse(User::class)]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function getAll(
        LoggerInterface $logger,
        GetAllUseCase $useCase,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetAllDto $dto = new GetAllDto(),
    ): JsonResponse {
        $useCase->setLogger($logger);
        $dataProvider = $useCase->execute($dto);
        return Response::successWithPagination(
            new SuccessWithPaginationResponse(
                PaginatedData::fromDataProvider(
                    $dataProvider,
                    User::fromUser(...),
                ),
            ),
        );
    }

    /** Экспорт пользователей в файл */
    #[Route('/users/export/{exportType<xlsx>}', 'users-export-to-file', methods: ['GET'])]
    #[IsGranted(PermissionEnum::UserExport->value, statusCode: 404)]
    #[OA\Tag('user')]
    #[LOA\FileResponse('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')]
    #[LOA\ErrorResponse(404)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function export(
        string $exportType,
        LoggerInterface $logger,
        GetAllUseCase $useCase,
        FileDataExportFactoryInterface $dataExportFactory,
        #[Autowire('%kernel.project_dir%')]
        string $projectDir,
        #[MapQueryString(validationFailedStatusCode: 422)]
        GetAllDto $dto = new GetAllDto(),
    ): BinaryFileResponse|JsonResponse {
        try {
            $dataExportFactory->setLogger($logger);
            $dataExport = $dataExportFactory->get($exportType);
            if (!is_dir("$projectDir/export/$exportType")) {
                mkdir("$projectDir/export/$exportType", 0777, true);
            }
            $exportFileName = 'users_' . (string)time() . ".$exportType";
            $fullExportFileName = "$projectDir/export/$exportType/$exportFileName";
            $dataExport->setFile($fullExportFileName);
        } catch (Throwable $e) {
            return Response::notFound();
        }

        $dataProvider = $useCase->execute($dto);
        if ($dataProvider->getTotal() === 0) {
            return Response::notFound();
        }

        $rows = [
            ['Почта', 'ФИО', 'Группа'],
        ];
        foreach ($dataProvider->getItems() as $user) {
            $rows[] = [
                $user->getEmail()->getEmail(),
                $user->getData()?->getFullName(),
                $user->getData()?->getGroup()?->getGroup()->getName(),
            ];
        }

        if ($dataExport->exportArray($rows)) {
            return $this->file(
                $fullExportFileName,
                $exportFileName,
            );
        }
        return Response::error(
            new ErrorResponse(
                new Error(
                    ErrorSlugEnum::InternalServerError->getSlug(),
                    'Не удалось отправить файл',
                ),
            ),
        );
    }
}
