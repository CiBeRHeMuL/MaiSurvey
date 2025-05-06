<?php

namespace App\Presentation\Console\Command;

use App\Application\UseCase\Auth\ResetPasswordHardUseCase;
use App\Domain\Enum\ValidationErrorSlugEnum;
use App\Domain\Exception\ValidationException;
use App\Domain\Validation\ValidationError;
use App\Domain\ValueObject\Email;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

#[AsCommand('user:reset-password')]
class ResetPasswordCommand extends AbstractCommand
{
    private LoggerInterface $logger;

    public function __construct(
        private ResetPasswordHardUseCase $useCase,
        LoggerInterface $logger,
    ) {
        $this->setLogger($logger);
        parent::__construct();
    }

    public function setLogger(LoggerInterface $logger): ResetPasswordCommand
    {
        $this->logger = $logger;
        $this->useCase->setLogger($this->logger);
        return $this;
    }

    protected function configure(): void
    {
        $this->setDescription('Принудительный сброс пароля')
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'Почта',
            )
            ->addArgument(
                'new_password',
                InputArgument::REQUIRED,
                'Новый пароль',
            );
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('new_password');
        if (!is_string($password)) {
            throw ValidationException::new([
                new ValidationError(
                    'new_password',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Пароль должен быть строкой',
                ),
            ]);
        }
        try {
            $email = new Email($email);
        } catch (Throwable $e) {
            throw ValidationException::new([
                new ValidationError(
                    'email',
                    ValidationErrorSlugEnum::WrongField->getSlug(),
                    'Неверный формат почты',
                ),
            ]);
        }
        $user = $this->useCase->execute($email, $password);
        $this->io->writeln('<fg=green>Пароль успешно сброшен, пользователь должен обновить его при следующем входе</>');
        return self::SUCCESS;
    }
}
