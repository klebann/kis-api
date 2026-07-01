<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class BorrowBookRequest
{
    #[Assert\NotBlank(message: 'Library card number is required')]
    #[Assert\Regex(
        pattern: '/^\d{6}$/',
        message: 'Library card number must be exactly 6 digits'
    )]
    public string $libraryCardNumber;
}
