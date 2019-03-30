<?php
declare(strict_types = 1);

namespace Innmind\Git\Repository;

use Innmind\Git\{
    Repository\Tag\Name,
    Message
};
use Innmind\TimeContinuum\PointInTimeInterface;

final class Tag
{
    private $name;
    private $message;
    private $date;

    public function __construct(Name $name, Message $message, PointInTimeInterface $date)
    {
        $this->name = $name;
        $this->message = $message;
        $this->date = $date;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function message(): Message
    {
        return $this->message;
    }

    public function date(): PointInTimeInterface
    {
        return $this->date;
    }
}
