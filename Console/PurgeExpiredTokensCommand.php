<?php

namespace Yivoff\JwtRefreshBundle\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Yivoff\JwtRefreshBundle\Contracts\PurgableRefreshTokenProviderInterface;
use Yivoff\JwtRefreshBundle\Contracts\RefreshTokenProviderInterface;

class PurgeExpiredTokensCommand extends Command
{
    protected static $defaultName = 'yivoff:jwt_refresh:purge_expired_tokens';

    public function __construct(private RefreshTokenProviderInterface $provider)
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Silently deletes all the expired Refresh Tokens (if any) from the configured Provider');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ( ! $this->provider instanceof PurgableRefreshTokenProviderInterface) {
            (new SymfonyStyle($input, $output))->error('Cannot proceed. Your provider class needs to implement \'' . PurgableRefreshTokenProviderInterface::class . '\'');

            return Command::FAILURE;
        }

        $this->provider->purgeExpiredTokens();

        return Command::SUCCESS;
    }
}
