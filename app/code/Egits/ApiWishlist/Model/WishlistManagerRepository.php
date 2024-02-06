<?php

namespace Egits\ApiWishlist\Model;

use Egits\ApiWishlist\Api\WishlistManagerInterface;

class WishlistManagerRepository implements WishlistManagerInterface
{
    /**
     * Get customer token by customer ID.
     *
     * @param int $customerId
     * @return int
     */
    public function getToken($customerId)
    {
        return $customerId;
    }
}
