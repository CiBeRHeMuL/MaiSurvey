<?php

namespace App\Presentation\Web\Controller;

use App\Application\Dto\Auth\ChangePasswordDto;
use App\Application\Dto\Auth\SignInDto;
use App\Application\Dto\Auth\SignUpStep1Dto;
use App\Application\Dto\Auth\SignUpStep2Dto;
use App\Application\UseCase\Auth\ChangePasswordUseCase;
use App\Application\UseCase\Auth\RefreshCredentialsUseCase;
use App\Application\UseCase\Auth\SignInUseCase;
use App\Application\UseCase\Auth\SignUpStep1UseCase;
use App\Application\UseCase\Auth\SignUpStep2UseCase;
use App\Domain\Dto\Auth\RefreshCredentialsDto as DomainRefreshCredentialsDto;
use App\Domain\Enum\UserStatusEnum;
use App\Domain\Exception\ErrorException;
use App\Domain\Service\Jwt\JwtServiceInterface;
use App\Domain\Service\Jwt\UserJwtClaims;
use App\Presentation\Web\Dto\Auth\RefreshCredentialsDto;
use App\Presentation\Web\Enum\ErrorSlugEnum;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Common\Error;
use App\Presentation\Web\Response\Model\Common\ErrorResponse;
use App\Presentation\Web\Response\Model\Common\SuccessResponse;
use App\Presentation\Web\Response\Model\SigningInUser;
use App\Presentation\Web\Response\Model\User;
use App\Presentation\Web\Response\Model\UserCredentials;
use App\Presentation\Web\Response\Response;
use App\Presentation\Web\Service\Security\SecurityService;
use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

class AuthController extends BaseController
{
    /** Вход в приложение. */
    #[Route('/auth/sign-in', 'sign-in', methods: ['POST'])]
    #[OA\Post(security: [])]
    #[OA\Tag('auth')]
    #[LOA\SuccessResponse(SigningInUser::class)]
    #[LOA\ErrorResponse]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function signIn(
        #[MapRequestPayload('json')]
        SignInDto $dto,
        SignInUseCase $useCase,
        LoggerInterface $logger,
        SecurityService $securityService,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $user = $useCase->execute($dto);
        return Response::success(
            new SuccessResponse(
                new SigningInUser(
                    $securityService->getCredentialsForUser($user),
                    User::fromUser($user),
                ),
            ),
        );
    }

    /** Регистрация в приложении. Шаг 1 */
    #[Route('/auth/sign-up/1', 'sign-up-1', methods: ['POST'])]
    #[OA\Post(security: [])]
    #[OA\Tag('auth')]
    #[LOA\SuccessResponse(SigningInUser::class)]
    #[LOA\ErrorResponse]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function signUpStep1(
        #[MapRequestPayload('json')]
        SignUpStep1Dto $dto,
        SignUpStep1UseCase $useCase,
        LoggerInterface $logger,
        SecurityService $securityService,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $user = $useCase->execute($dto);
        return Response::success(
            new SuccessResponse(
                new SigningInUser(
                    $securityService->getCredentialsForUser($user),
                    User::fromUser($user),
                ),
            ),
        );
    }

    /** Регистрация в приложении. Шаг 2 (Персональные данные) */
    #[IsGranted(UserStatusEnum::Active->value, statusCode: 401, exceptionCode: 401)]
    #[Route('/auth/sign-up/2', 'sign-up-2', methods: ['POST'])]
    #[OA\Tag('auth')]
    #[LOA\SuccessResponse(User::class)]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function signUpStep2(
        #[MapRequestPayload('json')]
        SignUpStep2Dto $dto,
        SignUpStep2UseCase $useCase,
        LoggerInterface $logger,
    ): JsonResponse {
        $useCase->setLogger($logger);
        $user = $useCase->execute($this->getUser()->getUser(), $dto);
        return Response::success(
            new SuccessResponse(User::fromUser($user)),
        );
    }

    /** Обновить токен доступа. */
    #[Route('/auth/refresh', 'refresh-credentials', methods: ['PUT'])]
    #[OA\Put(security: [])]
    #[OA\Tag('auth')]
    #[LOA\SuccessResponse(UserCredentials::class)]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function refreshCredentials(
        #[MapRequestPayload('json')]
        RefreshCredentialsDto $dto,
        JwtServiceInterface $jwtService,
        RefreshCredentialsUseCase $useCase,
        SecurityService $securityService,
        LoggerInterface $logger,
    ): JsonResponse {
        try {
            $claims = $jwtService->decode($dto->refresh_token, UserJwtClaims::class, 'user');
        } catch (Throwable) {
            $claims = null;
        }

        if ($claims === null || $claims->getExpiredAt()->diff(new DateTimeImmutable())->invert === 0) {
            return Response::error(
                new ErrorResponse(
                    new Error(
                        ErrorSlugEnum::Unauthorized->getSlug(),
                        'Некорректный токен',
                    ),
                ),
            );
        }

        $useCase->setLogger($logger);
        $user = $useCase->execute(
            new DomainRefreshCredentialsDto(
                $claims->getId(),
                $claims->getToken(),
                $claims->getExpiredAt(),
            ),
        );
        return Response::success(
            new SuccessResponse(
                $securityService->getCredentialsForUser($user),
            ),
        );
    }

    /** Сменить пароль */
    #[Route('/auth/change-password', 'change-password', methods: ['POST'])]
    #[IsGranted(UserStatusEnum::Active->value, statusCode: 401, exceptionCode: 401)]
    #[OA\Tag('auth')]
    #[LOA\SuccessResponse('boolean')]
    #[LOA\ErrorResponse(400)]
    #[LOA\ErrorResponse(401)]
    #[LOA\ValidationResponse]
    #[LOA\ErrorResponse(500)]
    public function changePassword(
        ChangePasswordUseCase $useCase,
        LoggerInterface $logger,
        #[MapRequestPayload('json')]
        ChangePasswordDto $dto,
    ): JsonResponse {
        if ($this->getUser() === null) {
            throw ErrorException::new(ErrorSlugEnum::Unauthorized->getSlug(), 401);
        }
        if ($this->getUser()->getUser()->isActive() === false) {
            throw ErrorException::new(ErrorSlugEnum::Unauthorized->getSlug(), 404);
        }

        $useCase->setLogger($logger);
        $useCase->execute($this->getUser()->getUser(), $dto);
        return Response::success(new SuccessResponse(true));
    }
}
