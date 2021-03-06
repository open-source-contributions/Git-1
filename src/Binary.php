<?php
declare(strict_types = 1);

namespace Innmind\Git;

use Innmind\Git\Exception\CommandFailed;
use Innmind\Server\Control\{
    Server,
    Server\Command,
    Server\Process\Output,
};
use Innmind\Url\Path;

final class Binary
{
    private Server $server;
    private Command $command;

    public function __construct(Server $server, Path $path)
    {
        $this->server = $server;
        $this->command = Command::foreground('git')
            ->withWorkingDirectory($path);
    }

    public function command(): Command
    {
        return $this->command;
    }

    public function __invoke(Command $command): Output
    {
        $process = $this
            ->server
            ->processes()
            ->execute($command);
        $process->wait();

        if (!$process->exitCode()->isSuccessful()) {
            throw new CommandFailed($command, $process);
        }

        return $process->output();
    }
}
