<?php
declare(strict_types=1);

namespace Ezzar\ExtraPrice\Plugin;

use Hyva\Theme\ViewModel\ProductListItem;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class AddExtraPricesToProductCard
{
    public function __construct(
        private readonly PriceHelper $priceHelper
    ) {
    }

    /**
     * Prepend inline RRP/WAS/WAS chips before Hyvä's original price HTML.
     */
    public function afterGetProductPriceHtml(
        ProductListItem $subject,
        string $result,
        ProductInterface $product,
        ...$args
    ): string {
        // Attributes:
        // RRP => price, WAS => extra_price, NOW => special_price
        $rrp        = $this->toFloat($product->getData('price'));
        $was        = $this->toFloat($product->getData('extra_price'));
        $nowSpecial = $this->toFloat($product->getData('special_price'));
        $duplicateNowPrice = $this->isDuplicateNowPrice($rrp, $was, $nowSpecial);
        if (!$duplicateNowPrice && $this->configurableChildrenHaveDuplicateNowPrice($product)) {
            $duplicateNowPrice = true;
        }

        if ($rrp === null && $was === null && $nowSpecial === null) {
            return $result;
        }

        $fmt = fn(?float $v) => $v !== null? preg_replace('/([.,]00)(?=(?:<\/span>)?\s*[^0-9]*$)/', '', $this->priceHelper->currency($v, true, false)): null;

        $parts = [];
        if ($rrp !== null && ! $duplicateNowPrice) {
            $parts[] = '<span class="line-through text-gray-700">RRP: ' . $fmt($rrp) . '</span>';
        }
        if ($was !== null && ! $this->pricesMatch($was, $nowSpecial)) {
            $parts[] = '<span class="line-through text-gray-700">Was: ' . $fmt($was) . '</span>';
        }
      /*  if ($nowSpecial !== null) {

            $parts[] = '<span class="line-through text-gray-700">Was: ' . $fmt($nowSpecial) . '</span>';
        }*/

        // same line
       /* $prepend = $parts
            ? '<div class="mb-1 flex flex-wrap items-baseline gap-3">' . implode('', $parts) . '</div>'
            : '';;*/
        //separate lines
        $prepend = $parts
            ? '<div class="flex flex-col gap-0.5">' . implode('', $parts) . '</div>'
            : '';

        return $prepend . $result;
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

    private function isDuplicateNowPrice(?float $rrp, ?float $was, ?float $now): bool
    {
        return $this->pricesMatch($rrp, $now) && ($was === null || $this->pricesMatch($was, $now));
    }

    private function configurableChildrenHaveDuplicateNowPrice(ProductInterface $product): bool
    {
        if ($product->getTypeId() !== 'configurable' || !method_exists($product, 'getTypeInstance')) {
            return false;
        }

        $childProducts = $product->getTypeInstance()
            ->getUsedProductCollection($product)
            ->addAttributeToSelect(['price', 'extra_price', 'special_price'])
            ->setStoreId($product->getStoreId());

        $hasChild = false;
        foreach ($childProducts as $childProduct) {
            $hasChild = true;
            $rrp = $this->toFloat($childProduct->getData('price'));
            $was = $this->toFloat($childProduct->getData('extra_price'));
            $now = $this->toFloat($childProduct->getData('special_price'));

            if (!$this->isDuplicateNowPrice($rrp, $was, $now)) {
                return false;
            }
        }

        return $hasChild;
    }
}
