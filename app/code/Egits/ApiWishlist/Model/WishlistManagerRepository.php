<?php

namespace Egits\ApiWishlist\Model;

use Egits\ApiWishlist\Api\WishlistManagerInterface;
use Magento\Wishlist\Model\Wishlist as WishlistFactory;

class WishlistManagerRepository implements WishlistManagerInterface
{

    /**
     * @var WishlistFactory
     */
    private $wishlist;

    /**
     * @param WishlistFactory $wishlist
     */
    public function __construct(
        WishlistFactory $wishlist
    ) {
        $this->wishlist = $wishlist;
    }


    /**
     * Get customer token by customer ID.
     *
     * @param int $customerId
     * @return array
     */
    public function getWishlistItems($customerId)
    {
        // Load the wishlist based on the customer ID
        $customerWishlist = $this->getWishlistFromCustomerId($customerId);

        // Get the item collection from the wishlist
        $wishlistItems = $customerWishlist->getItemCollection()->getData();

        // Initialize an array to store wishlist item data
        $wishlistData = [];

        return $wishlistItems;
    }

    public function getWishlistFromCustomerId($customerId)
    {
        return $this->wishlist->loadByCustomerId($customerId, true); // If signed in
    }
}
