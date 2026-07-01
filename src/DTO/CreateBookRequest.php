<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CreateBookRequest
{
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d{6}$/', message: 'Serial number must be exactly 6 digits')]
    public string $serialNumber;

    #[Assert\NotBlank]
    public string $title;

    #[Assert\NotBlank]
    public string $author;
}
