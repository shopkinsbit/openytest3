<?php

namespace Drupal\Tests\blazy\FunctionalJavascript;

use Drupal\filter\Entity\FilterFormat;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\blazy\Traits\BlazyUnitTestTrait;
use Drupal\Tests\blazy\Traits\BlazyCreationTestTrait;

/**
 * Tests the Blazy Filter JavaScript using PhantomJS, or Chromedriver.
 *
 * @group blazy
 */
class BlazyFilterJavaScriptTest extends WebDriverTestBase {

  use BlazyUnitTestTrait;
  use BlazyCreationTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field',
    'filter',
    'image',
    'media',
    'node',
    'text',
    'blazy',
    'blazy_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->setUpVariables();

    $this->entityManager          = $this->container->get('entity.manager');
    $this->entityFieldManager     = $this->container->get('entity_field.manager');
    $this->formatterPluginManager = $this->container->get('plugin.manager.field.formatter');
    $this->blazyAdmin             = $this->container->get('blazy.admin');
    $this->blazyManager           = $this->container->get('blazy.manager');

    // Create a text format.
    $full_html = FilterFormat::create([
      'format' => 'full_html',
      'name' => 'Full HTML',
      'weight' => 0,
    ]);
    $full_html->save();

    // Enable the Blazy filter.
    $this->filterFormatFull = FilterFormat::load('full_html');
    $this->filterFormatFull->setFilterConfig('blazy_filter', [
      'status' => TRUE,
    ]);
    $this->filterFormatFull->save();

    $this->setUpRealImage();
  }

  /**
   * Test the Blazy filter has media-wrapper--blazy for IMG and IFRAME elements.
   */
  public function testFilterDisplay() {
    $image_path = $this->getImagePath(TRUE);

    // Prevents execessive width with aspect ratio.
    $settings['extra_text'] = '<div style="width: 640px;">';
    $settings['extra_text'] .= '<img data-unblazy src="' . $this->dummyUrl . '" width="320" height="320" />';
    $settings['extra_text'] .= '<iframe src="https://www.youtube.com/watch?v=uny9kbh4iOEd" width="640" height="360"></iframe>';
    $settings['extra_text'] .= '<img src="' . $this->dummyUrl . '" width="320" height="320" />';
    $settings['extra_text'] .= '<img src="https://www.drupal.org/files/project-images/slick-carousel-drupal.png" width="215" height="162" />';
    $settings['extra_text'] .= '</div>';

    $this->setUpContentTypeTest($this->bundle);
    $this->setUpContentWithItems($this->bundle, $settings);

    $session = $this->getSession();
    $page = $session->getPage();

    $this->drupalGet('node/' . $this->entity->id());

    // Ensures Blazy is not loaded on page load.
    $this->assertSession()->elementNotExists('css', '.b-loaded');

    // Capture the initial page load moment.
    $this->createScreenshot($image_path . '/1_blazy_filter_initial.png');
    $this->assertSession()->elementExists('css', '.b-lazy');

    // Trigger Blazy to load images by scrolling down window.
    $session->executeScript('window.scrollTo(0, document.body.scrollHeight);');

    // Capture the loading moment after scrolling down the window.
    $this->createScreenshot($image_path . '/2_blazy_filter_loading.png');

    // Verifies that our filter works identified by media-wrapper--blazy class.
    $this->assertSession()->elementExists('css', '.media-wrapper--blazy');
    $this->assertSession()->elementContains('css', '.media-wrapper--blazy', 'b-lazy');

    // Also verifies that [data-unblazy] should not be touched, nor lazyloaded.
    $this->assertSession()->elementNotContains('css', '.media-wrapper--blazy', 'data-unblazy');

    // Wait a moment.
    $session->wait(3000);

    // Verifies that one of the images is there once loaded.
    $this->assertNotEmpty($this->assertSession()->waitForElement('css', '.b-loaded'));

    $loaded = $this->assertSession()->waitForElementVisible('css', '.b-loaded');
    $this->assertNotEmpty($loaded);

    // Capture the loaded moment.
    // The screenshots are at sites/default/files/simpletest/blazy.
    $this->createScreenshot($image_path . '/3_blazy_filter_loaded.png');
  }

}
