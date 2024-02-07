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

    /**
     * Undocumented function
     *
     * @param int $customerId
     * @param mixed $productId
     * @return int|null
     */
    public function addProductToWishlist($customerId, $productId);
}
