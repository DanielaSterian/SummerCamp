<?php


namespace App\Service;

use App\Entity\User;

class CounterService
{
    /**
     * @param User $user
     * @return int|null
     */
    public function countLP(User $user): ?int
    {
        return count($user->getLicensePlates());
    }
}