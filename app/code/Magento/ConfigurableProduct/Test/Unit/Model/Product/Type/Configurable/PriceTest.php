<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Type\Configurable;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class PriceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->model = $this->objectManagerHelper->getObject(
            '\Magento\ConfigurableProduct\Model\Product\Type\Configurable\Price'
        );
    }

    public function testGetFinalPrice()
    {
        $finalPrice = 10;
        $qty = 1;
        $configurableProduct = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getCustomOption', 'setSelectedConfigurableOption', 'setFinalPrice', '__wakeUp'])
            ->getMock();
        $childProduct = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getPriceInfo', '__wakeUp'])
            ->getMock();
        $customOption = $this->getMockBuilder('Magento\Catalog\Model\Product\Configuration\Item\Option')
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();
        $priceInfo = $this->getMockBuilder('Magento\Framework\Pricing\PriceInfo\Base')
            ->disableOriginalConstructor()
            ->setMethods(['getPrice'])
            ->getMock();
        $price = $this->getMockBuilder('Magento\Framework\Pricing\Price\PriceInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $amount = $this->getMockBuilder('Magento\Framework\Pricing\Amount\AmountInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $configurableProduct->expects($this->at(0))
            ->method('getCustomOption')
            ->with('simple_product')
            ->willReturn($customOption);
        $configurableProduct->expects($this->at(1))
            ->method('getCustomOption')
            ->with('simple_product')
            ->willReturn($customOption);
        $customOption->expects($this->once())->method('getProduct')->willReturn($childProduct);
        $configurableProduct->expects($this->once())
            ->method('setSelectedConfigurableOption')
            ->with($childProduct)
            ->willReturnSelf();
        $childProduct->expects($this->once())->method('getPriceInfo')->willReturn($priceInfo);
        $priceInfo->expects($this->once())->method('getPrice')->with('final_price')->willReturn($price);
        $price->expects($this->once())->method('getAmount')->willReturn($amount);
        $amount->expects($this->once())->method('getValue')->willReturn($finalPrice);
        $configurableProduct->expects($this->at(3))->method('getCustomOption')->with('option_ids')->willReturn(false);
        $configurableProduct->expects($this->once())->method('setFinalPrice')->with($finalPrice)->willReturnSelf();

        $this->assertEquals($finalPrice, $this->model->getFinalPrice($qty, $configurableProduct));
    }
}
