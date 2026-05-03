<?php

namespace App\Command;

use App\Entity\Athlete;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:create',
    description: 'Create a local application user.',
)]
final class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('displayName', InputArgument::REQUIRED, 'Athlete display name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = trim((string) $input->getArgument('email'));
        $displayName = trim((string) $input->getArgument('displayName'));

        if ($email === '') {
            $io->error('Email cannot be empty.');

            return Command::FAILURE;
        }

        if ($displayName === '') {
            $io->error('Display name cannot be empty.');

            return Command::FAILURE;
        }

        if ($this->userRepository->findOneBy(['email' => $email]) !== null) {
            $io->error(sprintf('A user with email "%s" already exists.', $email));

            return Command::FAILURE;
        }

        $question = new Question('Password: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $plainPassword = $io->askQuestion($question);

        if (!is_string($plainPassword) || $plainPassword === '') {
            $io->error('Password cannot be empty.');

            return Command::FAILURE;
        }

        $user = new User($email);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $athlete = new Athlete($user, $displayName);

        $this->entityManager->persist($user);
        $this->entityManager->persist($athlete);
        $this->entityManager->flush();

        $io->success(sprintf(
            'User "%s" created with athlete "%s".',
            $email,
            $displayName,
        ));

        return Command::SUCCESS;
    }
}
