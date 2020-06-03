<?php

namespace Drupal\simple_sitemap\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\simple_sitemap\Simplesitemap;

/**
 * Class SimplesitemapFormBase
 * @package Drupal\simple_sitemap\Form
 */
abstract class SimplesitemapFormBase extends ConfigFormBase {

  /**
   * @var \Drupal\simple_sitemap\Simplesitemap
   */
  protected $generator;

  /**
   * @var \Drupal\simple_sitemap\Form\FormHelper
   */
  protected $formHelper;

  /**
   * SimplesitemapFormBase constructor.
   * @param \Drupal\simple_sitemap\Simplesitemap $generator
   * @param \Drupal\simple_sitemap\Form\FormHelper $form_helper
   */
  public function __construct(
    Simplesitemap $generator,
    FormHelper $form_helper
  ) {
    $this->generator = $generator;
    $this->formHelper = $form_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('simple_sitemap.generator'),
      $container->get('simple_sitemap.form_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['simple_sitemap.settings'];
  }

  /**
   *
   */
  protected function getDonationText() {
    return '<div class="description">' . $this->t('If you would like to say thanks and support the development of this module, a <a target="_blank" href="@url">donation</a> will be much appreciated.', ['@url' => 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5AFYRSBLGSC3W']) . '</div>';
  }

}
