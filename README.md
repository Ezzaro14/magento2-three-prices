# Ezzar Extra Price

`Ezzar_ExtraPrice` adds RRP and previous-price output to Magento 2 Hyva product cards and product pages. It uses Magento's `price`, `extra_price`, and `special_price` product attributes.

<img width="280" height="105" alt="image" src="https://github.com/user-attachments/assets/91ac05b7-b36d-4340-b803-412d42abfda6" />
<img width="728" height="374" alt="image" src="https://github.com/user-attachments/assets/f87ed18b-2a03-4942-a90b-ec2baf36a06c" />


## Requirements

- Magento Open Source or Adobe Commerce 2.4.x
- PHP 8.1–8.4
- Hyva theme
- A decimal product attribute with code `extra_price`

## Installation

```bash
composer config repositories.ezzaro14-magento2-three-prices vcs https://github.com/Ezzaro14/magento2-three-prices.git
composer require ezzaro14/magento2-three-prices:dev-main
bin/magento module:enable Ezzar_ExtraPrice
bin/magento setup:upgrade
bin/magento cache:clean
```

The module treats `price` as RRP, `extra_price` as the previous price, and `special_price` as the current promotional price. Confirm that mapping matches your catalogue before enabling it.
