<?php

namespace App\Dto;

class PlayerDto
{
    private string $name;
    private ?string $session_id;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSessionId(): ?string
    {
        return $this->session_id;
    }

    public function setSessionId(?string $session_id): void
    {
        $this->session_id = $session_id;
    }
}