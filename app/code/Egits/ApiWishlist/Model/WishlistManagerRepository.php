<?php

namespace Egits\ApiWishlist\Model;

use Egits\ApiWishlist\Api\Data\WishlistManagerInterface;

class WishlistManagerRepository implements WishlistManagerInterface
{

    /**
     * This function returns the list of wishlist items
     *
     * @var array
     */
    public function getList()
    {
        return "working...";
    }
}
