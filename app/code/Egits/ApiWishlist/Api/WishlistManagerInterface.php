<?php

namespace Egits\ApiWishlist\Api;


interface WishlistManagerInterface
{
    /**
     * Get wishlist items from customer id
     *
     * @param int $customerId
     * @return array
     */
    public function getWishlistItems($customerId);

    /**
     * Add a new product to wishlist
     *
     * @param int $customerId
     * @param mixed $productId
     * @return int|null
     */
    public function addProductToWishlist($customerId, $productId);

    /**
     * Remove a product from wishlist
     *
     * @param int $customerId
     * @param mixed $productId
     * @return int|null
     */
    public function deleteSingleProductFromWishlist($customerId, $productId);
}
