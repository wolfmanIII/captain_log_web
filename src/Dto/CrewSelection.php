<?php

namespace App\Dto;

use App\Entity\Crew;

class CrewSelection
{
    public function __construct(
        public Crew $crew,
        public bool $selected = false
    ) {}
}
