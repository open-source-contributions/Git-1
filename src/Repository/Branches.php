<?php
declare(strict_types = 1);

namespace Innmind\Git\Repository;

use Innmind\Git\{
    Binary,
    Revision,
    Revision\Branch
};
use Innmind\Immutable\{
    Set,
    Str,
};

final class Branches
{
    private Binary $binary;

    public function __construct(Binary $binary)
    {
        $this->binary = $binary;
    }

    /**
     * @return Set<Branch>
     */
    public function local(): Set
    {
        $branches = Str::of(($this->binary)(
            $this
                ->binary
                ->command()
                ->withArgument('branch')
                ->withOption('no-color'),
        )->toString());

        return $branches
            ->split("\n")
            ->filter(static function(Str $line): bool {
                return !$line->matches('~HEAD detached~');
            })
            ->reduce(
                Set::of(Branch::class),
                static function(Set $branches, Str $branch): Set {
                    return $branches->add(new Branch(
                        $branch->substring(2)->toString(),
                    ));
                }
            );
    }

    /**
     * @return Set<Branch>
     */
    public function remote(): Set
    {
        $branches = Str::of(($this->binary)(
            $this
                ->binary
                ->command()
                ->withArgument('branch')
                ->withShortOption('r')
                ->withOption('no-color')
        )->toString());

        return $branches
            ->split("\n")
            ->filter(static function(Str $line): bool {
                return !$line->matches('~-> origin/~');
            })
            ->reduce(
                Set::of(Branch::class),
                static function(Set $branches, Str $branch): Set {
                    return $branches->add(new Branch(
                        $branch->substring(2)->toString(),
                    ));
                }
            );
    }

    /**
     * @return Set<Branch>
     */
    public function all(): Set
    {
        return $this
            ->local()
            ->merge($this->remote());
    }

    public function new(Branch $name, Revision $off = null): self
    {
        $command = $this
            ->binary
            ->command()
            ->withArgument('branch')
            ->withArgument($name->toString());

        if ($off) {
            $command = $command->withArgument($off->toString());
        }

        ($this->binary)($command);

        return $this;
    }

    public function delete(Branch $name): self
    {
        ($this->binary)(
            $this
                ->binary
                ->command()
                ->withArgument('branch')
                ->withShortOption('d')
                ->withArgument($name->toString())
        );

        return $this;
    }

    public function forceDelete(Branch $name): self
    {
        ($this->binary)(
            $this
                ->binary
                ->command()
                ->withArgument('branch')
                ->withShortOption('D')
                ->withArgument($name->toString())
        );

        return $this;
    }
}
