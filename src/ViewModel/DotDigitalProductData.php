<?php
/**
 * Dotdigital - https://dotdigital.com
 * Copyright © Dotdigital Group PLC 2024-present. All rights reserved.
 * This code may be used in conjunction with the module dotdigital/dotdigital-magento2-extension
 * See https://github.com/dotmailer/dotmailer-magento2-extension/blob/master/LICENSE.md
 */
declare(strict_types=1);

namespace Hyva\DotdigitalgroupEmail\ViewModel;

use Dotdigitalgroup\Email\Helper\Data as DotDigitalDataHelper;
use Dotdigitalgroup\Email\Model\Catalog\UrlFinder;
use Dotdigitalgroup\Email\Model\Product\ImageFinder;
use Dotdigitalgroup\Email\Model\Product\ImageType\Context\AbandonedBrowse as ImageType;
use Dotdigitalgroup\Email\Model\Product\PriceFinderFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class DotDigitalProductData implements ArgumentInterface
{
    /**
     * @var DotDigitalDataHelper
     */
    private $dotDigitalDataHelper;

    /**
     * @var PriceFinderFactory
     */
    private $priceFinderFactory;

    /**
     * @var ImageFinder
     */
    private $imageFinder;

    /**
     * @var ImageType
     */
    private $imageType;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlFinder
     */
    private $urlFinder;

    /**
     * DotDigitalProductData constructor.
     *
     * @param DotDigitalDataHelper $dotDigitalDataHelper
     * @param PriceFinderFactory $priceFinderFactory
     * @param ImageFinder $imageFinder
     * @param ImageType $imageType
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param UrlFinder $urlFinder
     */
    public function __construct(
        DotDigitalDataHelper $dotDigitalDataHelper,
        PriceFinderFactory $priceFinderFactory,
        ImageFinder $imageFinder,
        ImageType $imageType,
        CategoryRepositoryInterface $categoryRepository,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        UrlFinder $urlFinder
    ) {
        $this->dotDigitalDataHelper = $dotDigitalDataHelper;
        $this->priceFinderFactory = $priceFinderFactory;
        $this->imageFinder = $imageFinder;
        $this->imageType = $imageType;
        $this->categoryRepository = $categoryRepository;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->urlFinder = $urlFinder;
    }

    /**
     * Get data required for Dot Digital behaviour tracking.
     *
     * @param ProductInterface $product
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getData(ProductInterface $product): array
    {
        $priceFinder = $this->priceFinderFactory->create();
        $storeId = (int) $this->storeManager->getStore()->getId();
        $customerId = (int) $this->customerSession->getCustomerId();

        /** @var \Magento\Catalog\Model\Product $product */
        $specialPrice = $priceFinder->getSpecialPrice($product, $storeId);
        $basePrice = $priceFinder->getPrice($product, $storeId);
        return [
            'product_name' => $product->getName() ?? '',
            'product_url' => $this->getProductUrl($product),
            'product_currency' => $this->getCurrencyCode(),
            'product_status' => $product->getIsSalable() ? 'In Stock' : 'Out of Stock',
            'product_price' => (string) $basePrice,
            'product_price_incl_tax' => (string) $priceFinder->getPriceInclTax($product, $storeId, $customerId),
            'product_specialprice' => (string) $specialPrice,
            'product_specialPrice_incl_tax' => (string) $priceFinder->getSpecialPriceInclTax($product, $storeId, $customerId),
            'product_sku' => $product->getSku(),
            'product_brand' => $this->getBrand($product),
            'product_categories' => $this->getCategoryNames($product),
            'product_image_path' => $this->getImagePath($product),
            'product_description' => $this->getDescription($product),
            'product_type' => $this->getProductType($product)
        ];
    }

    /**
     * Get the full product URL.
     *
     * @param ProductInterface $product
     * @return string
     */
    private function getProductUrl(ProductInterface $product): string
    {
        try {
            /** @var \Magento\Catalog\Model\Product $product */
            return $this->urlFinder->fetchFor($product);
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * Get the currency code of the current store.
     *
     * @return string
     */
    private function getCurrencyCode()
    {
        try {
            $store = $this->storeManager->getStore();
            /** @var \Magento\Store\Model\Store $store */
            return $store->getBaseCurrencyCode();
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * Get the brand attribute value within the context of the website and respecting data field configuration.
     *
     * @param ProductInterface $product
     * @return string
     */
    private function getBrand(ProductInterface $product): string
    {
        try {
            $brandAttribute = $this->dotDigitalDataHelper->getBrandAttributeByWebsiteId(
                $this->storeManager->getStore()->getWebsiteId()
            );

            $brand = $product->getCustomAttribute($brandAttribute);

            return $brand ? $brand->getValue() : '';
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * Get the name of each category the product appears in.
     *
     * @param ProductInterface $product
     * @return array
     */
    private function getCategoryNames(ProductInterface $product): array
    {
        $categoryNames = [];

        /** @var \Magento\Catalog\Model\Product $product */
        $categoryIds = $product->getCategoryIds();

        foreach ($categoryIds as $categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
                $categoryNames[] = $category->getName();
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }

        return $categoryNames;
    }

    /**
     * Get product image path.
     *
     * @param ProductInterface $product
     * @return string
     */
    private function getImagePath(ProductInterface $product): string
    {
        try {
            return $this->imageFinder->getImageUrl(
                $product,
                $this->imageType->getImageType($this->storeManager->getStore()->getWebsiteId())
            );
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * Get the type of a product.
     *
     * This method retrieves the type of the product by calling the getTypeId method on the product object.
     * The type of the product can be 'simple', 'grouped', 'configurable', 'virtual', 'bundle', 'downloadable', etc.
     *
     * @param ProductInterface $product The product to get the type for.
     * @return string The type of the product.
     */
    private function getProductType(ProductInterface $product): string
    {
        return $product->getTypeId();
    }

    /**
     * Get product description.
     *
     * @param ProductInterface $product
     *
     * @return string
     */
    private function getDescription(ProductInterface $product): string
    {
        return $product->getCustomAttribute('description') ?
            strip_tags($product->getCustomAttribute('description')->getValue()) :
            '';
    }
}
