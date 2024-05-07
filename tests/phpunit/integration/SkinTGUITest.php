<?php
namespace MediaWiki\Skins\TGUI\Tests\Integration;

use Exception;
use MediaWiki\MediaWikiServices;
use MediaWiki\Skins\TGUI\SkinTGUI;
use MediaWikiIntegrationTestCase;
use ReflectionMethod;
use RequestContext;
use Title;
use Wikimedia\TestingAccessWrapper;

/**
 * Class TGUITemplateTest
 * @package MediaWiki\Skins\TGUI\Tests\Unit
 * @group TGUI
 * @group Skins
 */
class SkinTGUITest extends MediaWikiIntegrationTestCase {

	/**
	 * @return SkinTGUILegacy
	 */
	private function provideTGUITemplateObject() {
		$skinFactory = MediaWikiServices::getInstance()->getSkinFactory();
		$template = $skinFactory->makeSkin( 'tgui' );
		return $template;
	}

	/**
	 * @param string $nodeString an HTML of the node we want to verify
	 * @param string $tag Tag of the element we want to check
	 * @param string $attribute Attribute of the element we want to check
	 * @param string $search Value of the attribute we want to verify
	 * @return bool
	 */
	private function expectNodeAttribute( $nodeString, $tag, $attribute, $search ) {
		$node = new \DOMDocument();
		$node->loadHTML( $nodeString );
		$element = $node->getElementsByTagName( $tag )->item( 0 );
		if ( !$element ) {
			return false;
		}

		$values = explode( ' ', $element->getAttribute( $attribute ) );
		return in_array( $search, $values );
	}

	public function provideGetTocData() {
		$config = [
			'TGUITableOfContentsBeginning' => true,
			'TGUITableOfContentsCollapseAtCount' => 1
		];
		$tocData = [
			'number-section-count' => 2,
			'array-sections' => [
				[
					'toclevel' => 1,
					'level' => '2',
					'line' => 'A',
					'number' => '1',
					'index' => '1',
					'fromtitle' => 'Test',
					'byteoffset' => 231,
					'anchor' => 'A',
					'array-sections' =>	[],
					'is-top-level-section' => true,
					'is-parent-section' => false,
				],
				[
					'toclevel' => 1,
					'level' => '4',
					'line' => 'B',
					'number' => '2',
					'index' => '2',
					'fromtitle' => 'Test',
					'byteoffset' => 245,
					'anchor' => 'B',
					'array-sections' =>	[],
					'is-top-level-section' => true,
					'is-parent-section' => false,
				]
			]
		];
		$nestedTocData = [
			'number-section-count' => 2,
			'array-sections' => [
				[
					'toclevel' => 1,
					'level' => '2',
					'line' => 'A',
					'number' => '1',
					'index' => '1',
					'fromtitle' => 'Test',
					'byteoffset' => 231,
					'anchor' => 'A',
					'array-sections' => [
						'toclevel' => 2,
						'level' => '4',
						'line' => 'A1',
						'number' => '1.1',
						'index' => '2',
						'fromtitle' => 'Test',
						'byteoffset' => 245,
						'anchor' => 'A1',
						'array-sections' => [],
						'is-top-level-section' => false,
						'is-parent-section' => false,
					],
					'is-top-level-section' => true,
					'is-parent-section' => true,
				],
			]
		];

		$expectedConfigData = [
			'is-tgui-toc-beginning-enabled' => $config[ 'TGUITableOfContentsBeginning' ],
			'tgui-is-collapse-sections-enabled' =>
				$tocData[ 'number-section-count' ] >= $config[ 'TGUITableOfContentsCollapseAtCount' ]
		];
		$expectedNestedTocData = array_merge( $nestedTocData, $expectedConfigData );

		// qqx output
		$buttonLabel = '(tgui-toc-toggle-button-label: A)';
		$expectedNestedTocData[ 'array-sections' ][ 0 ][ 'tgui-button-label' ] = $buttonLabel;

		return [
			// When zero sections
			[
				[],
				$config,
				// TOC data is empty when given an empty array
				[]
			],
			// When number of multiple sections is lower than configured value
			[
				$tocData,
				array_merge( $config, [ 'TGUITableOfContentsCollapseAtCount' => 3 ] ),
				// 'tgui-is-collapse-sections-enabled' value is false
				array_merge( $tocData, $expectedConfigData, [
					'tgui-is-collapse-sections-enabled' => false
				] )
			],
			// When number of multiple sections is equal to the configured value
			[
				$tocData,
				array_merge( $config, [ 'TGUITableOfContentsCollapseAtCount' => 2 ] ),
				// 'tgui-is-collapse-sections-enabled' value is true
				array_merge( $tocData, $expectedConfigData )
			],
			// When number of multiple sections is higher than configured value
			[
				$tocData,
				array_merge( $config, [ 'TGUITableOfContentsCollapseAtCount' => 1 ] ),
				// 'tgui-is-collapse-sections-enabled' value is true
				array_merge( $tocData, $expectedConfigData )
			],
			// When "Beginning" TOC section is configured to be turned off
			[
				$tocData,
				array_merge( $config, [ 'TGUITableOfContentsBeginning' => false ] ),
				// 'is-tgui-toc-beginning-enabled' value is false
				array_merge( $tocData, $expectedConfigData, [
					'is-tgui-toc-beginning-enabled' => false
				] )
			],
			// When TOC has sections with top level parent sections
			[
				$nestedTocData,
				$config,
				// 'tgui-button-label' is provided for top level parent sections
				$expectedNestedTocData
			],
		];
	}

	/**
	 * @covers \MediaWiki\Skins\TGUI\SkinTGUI::getTocData
	 * @dataProvider provideGetTOCData
	 */
	public function testGetTocData(
		array $tocData,
		array $config,
		array $expected
	) {
		$this->overrideConfigValues( $config );
		$this->setUserLang( 'qqx' );

		$skinTGUI = new SkinTGUI( [ 'name' => 'tgui' ] );
		$openSkinTGUI = TestingAccessWrapper::newFromObject( $skinTGUI );
		$data = $openSkinTGUI->getTocData( $tocData );
		$this->assertEquals( $expected, $data );
	}

	/**
	 * @covers \MediaWiki\Skins\TGUI\SkinTGUI::getTemplateData
	 */
	public function testGetTemplateData() {
		$title = Title::newFromText( 'SkinTGUI' );
		$context = RequestContext::newExtraneousContext( $title );
		$context->setLanguage( 'fr' );
		$tguiTemplate = $this->provideTGUITemplateObject();
		$tguiTemplate->setContext( $context );
		$this->setTemporaryHook( 'SkinTemplateNavigation::Universal', [
			static function ( &$skinTemplate, &$content_navigation ) {
				$content_navigation['actions'] = [
					'action-1' => []
				];
				$content_navigation['namespaces'] = [
					'ns-1' => []
				];
				$content_navigation['variants'] = [
					[
						'class' => 'selected',
						'text' => 'Language variant',
						'href' => '/url/to/variant',
						'lang' => 'zh-hant',
						'hreflang' => 'zh-hant',
					]
				];
				$content_navigation['views'] = [];
				$content_navigation['user-menu'] = [
					'pt-1' => [ 'text' => 'pt1' ],
				];
			}
		] );
		$openTGUITemplate = TestingAccessWrapper::newFromObject( $tguiTemplate );

		$props = $openTGUITemplate->getTemplateData()['data-portlets'];
		$views = $props['data-views'];
		$namespaces = $props['data-namespaces'];

		// The mediawiki core specification might change at any time
		// so let's limit the values we test to those we are aware of.
		$keysToTest = [
			'id', 'class', 'html-tooltip', 'html-items',
			'html-after-portal', 'html-before-portal',
			'label', 'heading-class', 'is-dropdown'
		];
		foreach ( $views as $key => $value ) {
			if ( !in_array( $key, $keysToTest ) ) {
				unset( $views[ $key] );
			}
		}
		$this->assertSame(
			[
				// Provided by core
				'id' => 'p-views',
				'class' => 'mw-portlet mw-portlet-views emptyPortlet ' .
					'tgui-menu-tabs tgui-menu-tabs-legacy',
				'html-tooltip' => '',
				'html-items' => '',
				'html-after-portal' => '',
				'html-before-portal' => '',
				'label' => $context->msg( 'views' )->text(),
				'heading-class' => '',
				'is-dropdown' => false,
			],
			$views
		);

		$variants = $props['data-variants'];
		$actions = $props['data-actions'];
		$this->assertSame(
			'mw-portlet mw-portlet-namespaces tgui-menu-tabs tgui-menu-tabs-legacy',
			$namespaces['class']
		);
		$this->assertSame(
			'mw-portlet mw-portlet-variants tgui-menu-dropdown',
			$variants['class']
		);
		$this->assertSame(
			'mw-portlet mw-portlet-cactions tgui-menu-dropdown',
			$actions['class']
		);
		$this->assertSame(
			'mw-portlet mw-portlet-personal tgui-user-menu-legacy',
			$props['data-personal']['class']
		);
	}

	/**
	 * Standard config for Language Alert in Sidebar
	 * @return array
	 */
	private function enableLanguageAlertFeatureConfig(): array {
		return [
			'TGUILanguageInHeader' => [
				'logged_in' => true,
				'logged_out' => true
			],
			'TGUILanguageInMainPageHeader' => [
				'logged_in' => false,
				'logged_out' => false
			],
			'TGUILanguageAlertInSidebar' => [
				'logged_in' => true,
				'logged_out' => true
			],
		];
	}

	public function providerLanguageAlertRequirements() {
		$testTitle = Title::makeTitle( NS_MAIN, 'Test' );
		$testTitleMainPage = Title::makeTitle( NS_MAIN, 'MAIN_PAGE' );
		return [
			'When none of the requirements are present, do not show alert' => [
				// Configuration requirements for language in header and alert in sidebar
				[],
				// Title instance
				$testTitle,
				// Cached languages
				[],
				// Is the language selector at the top of the content?
				false,
				// Should the language button be hidden?
				false,
				// Expected
				false
			],
			'When the feature is enabled and languages should be hidden, do not show alert' => [
				$this->enableLanguageAlertFeatureConfig(),
				$testTitle,
				[], true, true, false
			],
			'When the language alert feature is disabled, do not show alert' => [
				[
					'TGUILanguageInHeader' => [
						'logged_in' => true,
						'logged_out' => true
					],
					'TGUILanguageAlertInSidebar' => [
						'logged_in' => false,
						'logged_out' => false
					]
				],
				$testTitle,
				[ 'fr', 'en', 'ko' ], true, false, false
			],
			'When the language in header feature is disabled, do not show alert' => [
				[
					'TGUILanguageInHeader' => [
						'logged_in' => false,
						'logged_out' => false
					],
					'TGUILanguageAlertInSidebar' => [
						'logged_in' => true,
						'logged_out' => true
					]
				],
				$testTitle,
				[ 'fr', 'en', 'ko' ], true, false, false
			],
			'When it is a main page, feature is enabled, and there are no languages, do not show alert' => [
				$this->enableLanguageAlertFeatureConfig(),
				$testTitleMainPage,
				[], true, true, false
			],
			'When it is a non-main page, feature is enabled, and there are no languages, do not show alert' => [
				$this->enableLanguageAlertFeatureConfig(),
				$testTitle,
				[], true, true, false
			],
			'When it is a main page, header feature is disabled, and there are languages, do not show alert' => [
				[
					'TGUILanguageInHeader' => [
						'logged_in' => false,
						'logged_out' => false
					],
					'TGUILanguageAlertInSidebar' => [
						'logged_in' => true,
						'logged_out' => true
					]
				],
				$testTitleMainPage,
				[ 'fr', 'en', 'ko' ], true, true, false
			],
			'When it is a non-main page, alert feature is disabled, there are languages, do not show alert' => [
				[
					'TGUILanguageInHeader' => [
						'logged_in' => true,
						'logged_out' => true
					],
					'TGUILanguageAlertInSidebar' => [
						'logged_in' => false,
						'logged_out' => false
					]
				],
				$testTitle,
				[ 'fr', 'en', 'ko' ], true, true, false
			],
			'When most requirements are present but languages are not at the top, do not show alert' => [
				$this->enableLanguageAlertFeatureConfig(),
				$testTitle,
				[ 'fr', 'en', 'ko' ], false, false, false
			],
			'When most requirements are present but languages should be hidden, do not show alert' => [
				$this->enableLanguageAlertFeatureConfig(),
				$testTitle,
				[ 'fr', 'en', 'ko' ], true, true, false
			],
			'When it is a main page, features are enabled, and there are languages, show alert' => [
				$this->enableLanguageAlertFeatureConfig(),
				$testTitleMainPage,
				[ 'fr', 'en', 'ko' ], true, false, true
			],
			'When all the requirements are present on a non-main page, show alert' => [
				$this->enableLanguageAlertFeatureConfig(),
				$testTitle,
				[ 'fr', 'en', 'ko' ], true, false, true
			],
		];
	}

	/**
	 * @dataProvider providerLanguageAlertRequirements
	 * @covers \MediaWiki\Skins\TGUI\SkinTGUI::shouldLanguageAlertBeInSidebar
	 * @param array $requirements
	 * @param Title $title
	 * @param array $getLanguagesCached
	 * @param bool $isLanguagesInContentAt
	 * @param bool $shouldHideLanguages
	 * @param bool $expected
	 * @throws Exception
	 */
	public function testShouldLanguageAlertBeInSidebar(
		array $requirements,
		Title $title,
		array $getLanguagesCached,
		bool $isLanguagesInContentAt,
		bool $shouldHideLanguages,
		bool $expected
	) {
		$this->overrideConfigValues( array_merge( $requirements, [
			'DefaultSkin' => 'tgui'
		] ) );

		$mockSkinTGUI = $this->getMockBuilder( SkinTGUI::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'getTitle', 'getLanguagesCached','isLanguagesInContentAt', 'shouldHideLanguages' ] )
			->getMock();
		$mockSkinTGUI->method( 'getTitle' )
			->willReturn( $title );
		$mockSkinTGUI->method( 'getLanguagesCached' )
			->willReturn( $getLanguagesCached );
		$mockSkinTGUI->method( 'isLanguagesInContentAt' )->with( 'top' )
			->willReturn( $isLanguagesInContentAt );
		$mockSkinTGUI->method( 'shouldHideLanguages' )
			->willReturn( $shouldHideLanguages );

		$shouldLanguageAlertBeInSidebarMethod = new ReflectionMethod(
			SkinTGUI::class,
			'shouldLanguageAlertBeInSidebar'
		);
		$shouldLanguageAlertBeInSidebarMethod->setAccessible( true );

		$this->assertSame(
			$expected,
			$shouldLanguageAlertBeInSidebarMethod->invoke( $mockSkinTGUI )
		);
	}
}
