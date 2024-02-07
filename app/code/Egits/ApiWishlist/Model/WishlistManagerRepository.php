<?php

namespace Egits\ApiWishlist\Model;

use Egits\ApiWishlist\Api\WishlistManagerInterface;
use Magento\Wishlist\Model\Wishlist as WishlistFactory;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResource;
use Magento\Catalog\Model\ProductRepository;


class WishlistManagerRepository implements WishlistManagerInterface
{

    /**
     * @var WishlistFactory
     */
    private $wishlist;

    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var WishlistResource
     */
    private $wishlistResource;

    /**
     * @param WishlistFactory $wishlist
     * @param ProductRepository $productRepository
     * @param WishlistResource $wishlistResource
     */
    public function __construct(
        ProductRepository $productRepository,
        WishlistResource $wishlistResource,
        WishlistFactory $wishlist
    ) {
        $this->wishlist = $wishlist;
        $this->productRepository = $productRepository;
        $this->wishlistResource = $wishlistResource;
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

    /**
     * Add wishlist item
     *
     * @param int $customerId
     * @param mixed $productId
     * @return int|null
     * @throws NoSuchEntityException
     */
    public function addProductToWishlist($customerId, $productId)
    {
        $productId = (int)$productId;
        $customerWishlist = $this->getWishlistFromCustomerId($customerId);

        try {
            $product = $this->productRepository->getById($productId);
            $wishlistProduct = $customerWishlist->addNewItem($product);
            $productName = $product->getName();
            // Save the wishlist
            // $customerWishlist->save();
            $this->wishlistResource->save($customerWishlist);

            return $productName . " has been added to wishlist";
        } catch (\Exception $e) {
            // Handle exception, e.g., if the product ID is invalid
            return null;
        }
        // $wishlistProduct = $customerWishlist->addNewItem($productId);
        // $customerWishlist->save();
        // return $wishlistProduct->getId();
    }

    /**
     * Get the wishlist data from customer id
     *
     * @param int $customerId
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function getWishlistFromCustomerId($customerId)
    {
        return $this->wishlist->loadByCustomerId($customerId, true); // If signed in
    }
}
