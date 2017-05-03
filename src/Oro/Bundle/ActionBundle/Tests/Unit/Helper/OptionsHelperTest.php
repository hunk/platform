<?php

namespace Oro\Bundle\ActionBundle\Tests\Unit\Helper;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\ActionBundle\Helper\OptionsHelper;
use Oro\Bundle\ActionBundle\Button\ButtonInterface;

class OptionsHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var Router|\PHPUnit_Framework_MockObject_MockObject */
    protected $router;

    /** @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $translator;

    /** @var OptionsHelper */
    protected $helper;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->router = $this->createMock(Router::class);
        $this->router->expects($this->any())->method('generate')->willReturn('generated-url');

        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function ($id) {
                return $id === 'untranslated' ? 'untranslated' : '[trans]'.$id.'[/trans]';
            });

        $this->helper = new OptionsHelper(
            $this->router,
            $this->translator
        );
    }

    /**
     * @param ButtonInterface $button
     * @param array $expectedData
     *
     * @dataProvider getFrontendOptionsProvider
     */
    public function testGetFrontendOptions(ButtonInterface $button, array $expectedData)
    {
        $this->assertEquals($expectedData, $this->helper->getFrontendOptions($button));
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getFrontendOptionsProvider()
    {
        $defaultData = [
            'options' => [
                'hasDialog' => null,
                'showDialog' => false,
                'executionUrl' => 'generated-url',
                'url' => 'generated-url',
                'jsDialogWidget' => ButtonInterface::DEFAULT_JS_DIALOG_WIDGET,
            ],
            'data' => [],
        ];

        return [
            'empty options' => [
                'button' => $this->getButton('test label', []),
                'expectedData' => $defaultData,
            ],
            'filled options' => [
                'button' => $this->getButton('test label', [
                    'hasForm' => true,
                    'hasDialog' => true,
                    'showDialog' => true,
                    'frontendOptions' => [
                        'title' => 'custom title',
                        'message' => [
                            'message' => 'message1',
                            'message_parameters' => ['param1' => 'value1'],
                        ],
                    ],
                    'buttonOptions' => [
                        'data' => [
                            'some' => 'data',
                        ],
                    ],
                ]),
                'expectedData' => [
                    'options' => [
                        'hasDialog' => true,
                        'showDialog' => true,
                        'dialogOptions' => [
                            'title' => '[trans]custom title[/trans]',
                            'dialogOptions' => [],
                        ],
                        'dialogUrl' => 'generated-url',
                        'executionUrl' => 'generated-url',
                        'url' => 'generated-url',
                        'jsDialogWidget' => ButtonInterface::DEFAULT_JS_DIALOG_WIDGET,
                    ],
                    'data' => [
                        'some' => 'data',
                    ],
                ],
            ],
            'options with message' => [
                'button' => $this->getButton('test label', [
                    'hasForm' => false,
                    'frontendOptions' => [
                        'message' => [
                            'message' => 'message1',
                            'message_parameters' => ['param1' => 'value1']
                        ],
                    ]
                ]),
                'expectedData' => [
                    'options' => [
                        'hasDialog' => false,
                        'showDialog' => false,
                        'executionUrl' => 'generated-url',
                        'url' => 'generated-url',
                        'jsDialogWidget' => ButtonInterface::DEFAULT_JS_DIALOG_WIDGET,
                        'message' => [
                            'title' => '[trans]test label[/trans]',
                            'content' => '[trans]message1[/trans]',
                        ],
                    ],
                    'data' => [],
                ],
            ],
            'options with untranslated message' => [
                'button' => $this->getButton('test label', [
                    'hasForm' => false,
                    'frontendOptions' => [
                        'message' => [
                            'message' => 'untranslated',
                            'message_parameters' => ['param1' => 'value1']
                        ],
                    ]
                ]),
                'expectedData' => [
                    'options' => [
                        'hasDialog' => false,
                        'showDialog' => false,
                        'executionUrl' => 'generated-url',
                        'url' => 'generated-url',
                        'jsDialogWidget' => ButtonInterface::DEFAULT_JS_DIALOG_WIDGET,
                    ],
                    'data' => [],
                ],
            ],
        ];
    }

    /**
     * @param string $label
     * @param array $templateData
     *
     * @return ButtonInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getButton($label, array $templateData)
    {
        $button = $this->createMock(ButtonInterface::class);
        $button->expects($this->any())->method('getTemplateData')->willReturn($templateData);
        $button->expects($this->any())->method('getLabel')->willReturn($label);

        return $button;
    }
}
