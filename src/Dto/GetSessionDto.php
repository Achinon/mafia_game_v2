<?php

namespace App\Dto;

class GetSessionDto
{
    private string $session_id;

    public function getSessionId(): string
    {
        return $this->session_id;
    }

    public function setSessionId(string $session_id): void
    {
        $this->session_id = $session_id;
    }
}