<?php
/**
 * Copyright © PART <info@part-online.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Part\NoCategoryFilter\Test\Unit\Model\Catalog\Layer;

use \Part\NoCategoryFilter\Model\Catalog\Layer\FilterList;

class FilterListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layerMock;

    /**
     * @var \Part\NoCategoryFilter\Model\Catalog\Layer\FilterList
     */
    protected $model;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder('\Magento\Framework\ObjectManagerInterface')
            ->getMock();
        $this->attributeListMock = $this->getMockBuilder('Magento\Catalog\Model\Layer\FilterableAttributeListInterface')
            ->setMethods(['getList'])
            ->disableAutoload()
            ->getMock();
        $this->attributeMock = $this->getMockBuilder('\Magento\Catalog\Model\ResourceModel\Eav\Attribute')
            ->setMethods(['getAttributeCode', 'getBackendType'])
            ->disableAutoload()
            ->getMock();
        $filters = [
            FilterList::CATEGORY_FILTER => 'CategoryFilterClass',
            FilterList::PRICE_FILTER => 'PriceFilterClass',
            FilterList::DECIMAL_FILTER => 'DecimalFilterClass',
            FilterList::ATTRIBUTE_FILTER => 'AttributeFilterClass',

        ];
        $this->layerMock = $this->getMockBuilder('\Magento\Catalog\Model\Layer')
            ->disableAutoload()
            ->getMock();

        $this->model = new FilterList($this->objectManagerMock, $this->attributeListMock, $filters);
    }

    /**
     * @param string $method
     * @param string $value
     * @param string $expectedClass
     * @dataProvider getFiltersDataProvider
     *
     * @covers \Part\NoCategoryFilter\Model\Catalog\Layer\FilterList::getFilters
     */
    public function testGetFilters($method, $value, $expectedClass)
    {
        $this->objectManagerMock->expects($this->at(0))
            ->method('create')
            ->with($expectedClass, [
                'data' => ['attribute_model' => $this->attributeMock],
                'layer' => $this->layerMock])
            ->will($this->returnValue('filter'));

        $this->attributeMock->expects($this->once())
            ->method($method)
            ->will($this->returnValue($value));

        $this->attributeListMock->expects($this->once())
            ->method('getList')
            ->will($this->returnValue([$this->attributeMock]));

        $this->assertEquals(['filter'], $this->model->getFilters($this->layerMock));
    }

    /**
     * @return array
     */
    public function getFiltersDataProvider()
    {
        return [
            [
                'method' => 'getAttributeCode',
                'value' => FilterList::PRICE_FILTER,
                'expectedClass' => 'PriceFilterClass',
            ],
            [
                'method' => 'getBackendType',
                'value' => FilterList::DECIMAL_FILTER,
                'expectedClass' => 'DecimalFilterClass',
            ],
            [
                'method' => 'getAttributeCode',
                'value' => null,
                'expectedClass' => 'AttributeFilterClass',
            ]
        ];
    }
}
