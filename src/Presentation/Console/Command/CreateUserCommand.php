<?php

namespace App\Presentation\Console\Command;

use App\Application\Dto\User\CreateFullUserDto;
use App\Application\UseCase\User\CreateUserUseCase;
use App\Domain\Service\Jwt\JwtServiceInterface;
use App\Domain\Service\Jwt\UserJwtClaims;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('user:create')]
class CreateUserCommand extends AbstractCommand
{
    private LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        private CreateUserUseCase $useCase,
        private JwtServiceInterface $jwtService,
    ) {
        $this->setLogger($logger);
        parent::__construct();
    }

    public function setLogger(LoggerInterface $logger): CreateUserCommand
    {
        $this->logger = $logger;
        $this->useCase->setLogger($logger);
        return $this;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Создание пользователя')
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Почта',
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'Пароль',
            )
            ->addArgument(
                'role',
                InputArgument::REQUIRED,
                'Роль',
            )
            ->addArgument(
                'first_name',
                InputArgument::REQUIRED,
                'Имя',
            )
            ->addArgument(
                'last_name',
                InputArgument::REQUIRED,
                'Фамилия',
            )
            ->addOption(
                'patronymic',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Отчество',
            )
            ->addOption(
                'group_id',
                'g',
                InputOption::VALUE_OPTIONAL,
                'Группа',
            )
            ->addOption(
                'need_change_password',
                'C',
                InputOption::VALUE_NONE | InputOption::VALUE_OPTIONAL,
                'Необходимо сменить пароль при первом входе',
                false,
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this
            ->useCase
            ->execute(
                new CreateFullUserDto(
                    $input->getArgument('email'),
                    $input->getArgument('password'),
                    $input->getArgument('role'),
                    $input->getArgument('first_name'),
                    $input->getArgument('last_name'),
                    $input->getOption('patronymic'),
                    $input->getOption('group_id'),
                    $input->getOption('need_change_password'),
                ),
            );

        $accessTokenClaims = new UserJwtClaims(
            $user->getId(),
            $user->getAccessToken(),
            $user->getAccessTokenExpiresAt(),
        );
        $refreshTokenClaims = new UserJwtClaims(
            $user->getId(),
            $user->getRefreshToken(),
            $user->getRefreshTokenExpiresAt(),
        );

        $this->io->writeln('<fg=green>Группа успешно создана</>');
        $this->io->horizontalTable(
            ['ID', 'Статус', 'Почта', 'ФИО', 'Группа', 'Токен доступа', 'Refresh токен'],
            [
                [
                    $user->getId()->toRfc4122(),
                    $user->getStatus()->value,
                    $user->getEmail()->getEmail(),
                    $user->getData()?->getFullName(),
                    $user->getData()?->getGroup()?->getGroup()?->getName(),
                    $this->jwtService->encode($accessTokenClaims),
                    $this->jwtService->encode($refreshTokenClaims),
                ],
            ],
        );
        return self::SUCCESS;
    }
}
