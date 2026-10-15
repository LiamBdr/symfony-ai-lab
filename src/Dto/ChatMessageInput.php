<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChatMessageInput
{
    public function __construct(
        #[Assert\NotBlank(normalizer: 'trim')]
        #[Assert\Length(max: 2000)]
        public string $message,
    ) {
    }
}
