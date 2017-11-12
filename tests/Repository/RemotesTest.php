<?php
declare(strict_types = 1);

namespace Tests\Innmind\Git\Repository;

use Innmind\Git\{
    Repository\Remotes,
    Repository\Remote,
    Repository\Remote\Url
};
use Innmind\Server\Control\{
    Server,
    Server\Processes,
    Server\Process,
    Server\Process\Output,
    Server\Process\ExitCode
};
use Innmind\Immutable\SetInterface;
use PHPUnit\Framework\TestCase;

class RemotesTest extends TestCase
{
    public function testAll()
    {
        $server = $this->createMock(Server::class);
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->method('exitCode')
            ->willReturn(new ExitCode(0));
        $process
            ->method('output')
            ->willReturn($output = $this->createMock(Output::class));
        $output
            ->expects($this->once())
            ->method('__toString')
            ->willReturn(<<<REMOTES
origin
gitlab
local
REMOTES
            );

        $remotes = new Remotes(
            $server,
            '/tmp/foo'
        );

        $all = $remotes->all();

        $this->assertInstanceOf(SetInterface::class, $all);
        $this->assertSame(Remote::class, (string) $all->type());
        $this->assertCount(3, $all);
        $this->assertSame('origin', (string) $all->current()->name());
        $all->next();
        $this->assertSame('gitlab', (string) $all->current()->name());
        $all->next();
        $this->assertSame('local', (string) $all->current()->name());
        $all->next();
    }

    public function testGet()
    {
        $remotes = new Remotes(
            $this->createMock(Server::class),
            'watev'
        );

        $remote = $remotes->get('origin');

        $this->assertInstanceOf(Remote::class, $remote);
        $this->assertSame('origin', (string) $remote->name());
    }

    public function testAdd()
    {
        $server = $this->createMock(Server::class);
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(function($command): bool {
                return (string) $command === 'git remote add origin git@github.com:Innmind/Git.git' &&
                    $command->workingDirectory() === '/tmp/foo';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->method('exitCode')
            ->willReturn(new ExitCode(0));

        $remotes = new Remotes(
            $server,
            '/tmp/foo'
        );

        $remote = $remotes->add('origin', new Url('git@github.com:Innmind/Git.git'));

        $this->assertInstanceOf(Remote::class, $remote);
        $this->assertSame('origin', (string) $remote->name());
    }

    public function testRemove()
    {
        $server = $this->createMock(Server::class);
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(function($command): bool {
                return (string) $command === 'git remote remove origin' &&
                    $command->workingDirectory() === '/tmp/foo';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->method('exitCode')
            ->willReturn(new ExitCode(0));

        $remotes = new Remotes(
            $server,
            '/tmp/foo'
        );

        $this->assertSame($remotes, $remotes->remove('origin'));
    }
}
