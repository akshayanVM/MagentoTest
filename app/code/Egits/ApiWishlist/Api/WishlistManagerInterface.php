<?php

namespace Egits\ApiWishlist\Api;


interface WishlistManagerInterface
{
    /**
     * Undocumented function
     *
     * @param int $customerId
     * @return array
     */
    public function getWishlistItems($customerId);
}
