<?php

namespace EnderLab\ToolsBundle\Service;

use App\System\Infrastructure\Symfony\Security\SecurityUser;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class PasswordHelper
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {

    }

    /**
     * @throws Exception
     */
    public function generatePassword(?string $password = null): string
    {
        if (null === $password) {
            $password = $this->generateStrongPassword();
        }

        $securityUser = new SecurityUser();

        return $this->passwordHasher->hashPassword($securityUser, $password);
    }

    private function generateStrongPassword(): string
    {
        $sets = array();
        $length = 9;
        $addDashes = false;
        $availableSets = 'luds';

        if(str_contains($availableSets, 'l')) {
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        }

        if(str_contains($availableSets, 'u')) {
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }

        if(str_contains($availableSets, 'd')) {
            $sets[] = '23456789';
        }

        if(str_contains($availableSets, 's')) {
            $sets[] = '!@#$%&*?_-';
        }

        $all = '';
        $password = '';

        foreach($sets as $set)
        {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }

        $all = str_split($all);

        for($i = 0; $i < $length - count($sets); $i++) {
            $password .= $all[array_rand($all)];
        }

        $password = str_shuffle($password);

        if(!$addDashes) {
            return $password;
        }

        $dashLen = floor(sqrt($length));
        $dashStr = '';

        while(strlen($password) > $dashLen) {
            $dashStr .= substr($password, 0, (int) $dashLen) . '-';
            $password = substr($password, (int) $dashLen);
        }

        $dashStr .= $password;

        return $dashStr;
    }
}
