<?php

namespace Egits\ApiWishlist\Model;

use Egits\ApiWishlist\Api\WishlistManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Model\Wishlist as WishlistFactory;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory as WishlistItemCollectionFactory;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResource;
use Magento\Catalog\Model\ProductRepository;
use Magento\Wishlist\Model\Wishlist\Data\WishlistItemFactory;
use Magento\Framework\Message\ManagerInterface;

class WishlistManagerRepository implements WishlistManagerInterface
{

    /**
     * @var WishlistFactory
     */
    private $wishlist;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var WishlistResource
     */
    private $wishlistResource;

    /**
     * @var WishlistItemCollectionFactory
     */
    private $wishlistItemFactory;

    /**
     * @param ProductRepository $productRepository
     * @param ManagerInterface $messageManager
     * @param WishlistResource $wishlistResource
     * @param WishlistItemCollectionFactory $wishlistItemFactory
     * @param WishlistFactory $wishlist
     */
    public function __construct(
        ProductRepository $productRepository,
        ManagerInterface $messageManager,
        WishlistResource $wishlistResource,
        WishlistItemCollectionFactory $wishlistItemFactory,
        WishlistFactory $wishlist
    ) {
        $this->wishlist = $wishlist;
        $this->messageManager = $messageManager;
        $this->productRepository = $productRepository;
        $this->wishlistResource = $wishlistResource;
        $this->wishlistItemFactory = $wishlistItemFactory;
    }

    /**
     * Get wishlist items list by customer ID.
     *
     * @param int $customerId
     * @return string|array
     * @throws NoSuchEntityException
     */
    public function getWishlistItems($customerId)
    {
        // Load the wishlist based on the customer ID
        $customerWishlist = $this->getWishlistFromCustomerId($customerId);

        // Get the item collection from the wishlist
        $wishlistItems = $customerWishlist->getItemCollection()->getData();

        // Initialize an array to store wishlist item data
        $wishlistData = [];

        // Check if the wishlist is empty
        if (empty($wishlistItems)) {
            return "Wishlist is empty for the customer.";
        }

        return $wishlistItems;
    }

    /**
     * Add wishlist item
     *
     * @param int $customerId
     * @param mixed $productId
     * @return int|null
     */
    public function addProductToWishlist($customerId, $productId)
    {
        $productId = (int)$productId;
        $customerWishlist = $this->getWishlistFromCustomerId($customerId);

        try {
            $product = $this->productRepository->getById($productId);

            // Check if the product is already in the wishlist
            $wishlistItems = $customerWishlist->getItemCollection();
            foreach ($wishlistItems as $wishlistItem) {
                if ($wishlistItem->getProductId() == $productId) {
                    $productName = $product->getName();
                    return  $productName . " is already in your wishlist";
                }
            }

            // Product not found in the wishlist, proceed to add it
            $wishlistProduct = $customerWishlist->addNewItem($product);
            $productName = $product->getName();

            // Save the updated wishlist
            $this->wishlistResource->save($customerWishlist);

            return $productName . " has been added to the wishlist";
        } catch (\Exception $e) {
            // Handle exception, e.g., if the product ID is invalid
            return null;
        }
    }

    /**
     * Delete a single product from the wishlist
     *
     * @param mixed $customerId
     * @param int $productId
     * @return int|null
     * @throws NoSuchEntityException
     */
    public function deleteSingleProductFromWishlist($customerId, $productId)
    {
        $productId = (int)$productId;
        $customerWishlist = $this->getWishlistFromCustomerId($customerId);
        $product = $this->productRepository->getById($productId);

        // Check if the product is in the wishlist
        $wishlistItems = $customerWishlist->getItemCollection();
        $productName = $product->getName();

        foreach ($wishlistItems as $wishlistItem) {
            if ($wishlistItem->getProductId() == $productId) {
                // Product found in the wishlist, remove it
                // $customerWishlist->removeItem($wishlistItem->getId());
                $wishlistItem->delete();
                return $productName . " has been deleted from the wishlist.";
            }
        }
        return "Product not found in the wishlist.";
    }

    /**
     * Delete all wishlist items from the wishlist
     *
     * @param int $customerId
     * @param int $productId
     * @return string
     * @throws NoSuchEntityException
     */
    public function deleteAllProductsFromWishlist($customerId, $productId)
    {
        try {
            $customerWishlist = $this->getWishlistFromCustomerId($customerId);
            $wishlistItems = $customerWishlist->getItemCollection();

            if ($wishlistItems->getSize() > 0) {
                // Delete all items from the wishlist
                foreach ($wishlistItems as $wishlistItem) {
                    $wishlistItem->delete();
                }

                // Save the updated wishlist
                $this->wishlistResource->save($customerWishlist);

                return "All items deleted from the wishlist.";
            } else {
                return "Wishlist is already empty.";
            }
        } catch (\Exception $e) {
            // Log the exception or handle it based on your requirements
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Wishlist not found for customer.'));
        }
    }

    /**
     * Update the quantity of an existing wishlist item
     *
     * @param array $wishlistData
     * @param int $customerId
     * @return int|string|null
     */
    public function setWishlistItemQuantity(array $wishlistData, $customerId)
    {
        try {
            if (isset($wishlistData['product_id'], $wishlistData['qty'])) {
                $productId = (int)$wishlistData['product_id'];
                $quantity = (int)$wishlistData['qty'];

                // Load the customer's wishlist
                $customerWishlist = $this->getWishlistFromCustomerId($customerId);

                // Check if the product is in the wishlist
                $wishlistItem = $customerWishlist->getItemCollection()
                    // main_table is an alias for wishlist_item table ( whiich is the main table )
                    ->addFieldToFilter('main_table.product_id', $productId) // Specify the table alias for product_id
                    ->setPageSize(1)
                    ->getFirstItem();

                if ($wishlistItem->getId()) {
                    // Update the quantity if the item exists
                    $wishlistItem->setQty($quantity);
                    $this->wishlistResource->save($customerWishlist);

                    // Display success message
                    $this->messageManager
                        ->addSuccessMessage("Quantity updated for product with ID $productId to $quantity");

                    return "Quantity updated for product with ID $productId to $quantity";
                } else {
                    return "Product with ID $productId not found in the wishlist.";
                }
            } else {
                return "Invalid data structure. Please provide 'wishlistData' with 'product_id' and 'qty'.";
            }
        } catch (\Exception $e) {
            // Handle exceptions if necessary
            return "Error updating wishlist item quantity: " . $e->getMessage();
        }
    }

    /**
     * Get the wishlist data from customer id
     *
     * @param int $customerId
     * @return WishlistFactory
     */
    public function getWishlistFromCustomerId($customerId)
    {
        return $this->wishlist->loadByCustomerId($customerId, true); // If signed in
    }
}
