<?php
namespace Ezzar\ExtraPrice\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;

class ExtraPrice extends Template
{
    public function __construct(
        Template\Context $context,
        private Registry $registry,
        private PricingHelper $pricingHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getProduct(): ?\Magento\Catalog\Api\Data\ProductInterface
    {
        return $this->registry->registry('current_product');
    }

    public function getExtraPrice(): ?float
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }
        $extraPrice = $this->toFloat($product->getData('extra_price'));
        if ($extraPrice === null || $extraPrice <= 0) {
            return null;
        }

        $specialPrice = $this->toFloat($product->getData('special_price'));
        if ($this->pricesMatch($extraPrice, $specialPrice)) {
            return null;
        }

        return $extraPrice;
    }

    public function isSpecialActive(): bool
    {
        $product = $this->getProduct();
        if (!$product) {
            return false;
        }

        $regular = (float)$product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        $final   = (float)$product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();

        return $final < $regular;
    }

    public function formatPrice(float $amount): string
    {
        return $this->pricingHelper->currency($amount, false, false);
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function pricesMatch(?float $firstPrice, ?float $secondPrice): bool
    {
        if ($firstPrice === null || $secondPrice === null) {
            return false;
        }

        return $firstPrice === $secondPrice;
    }
}
