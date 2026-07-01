<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateBookRequest
{
    #[Assert\Regex(pattern: '/^\d{6}$/', message: 'Serial number must be exactly 6 digits')]
    public ?string $serialNumber = null;

    #[Assert\Length(min: 1)]
    public ?string $title = null;

    #[Assert\Length(min: 1)]
    public ?string $author = null;
}
