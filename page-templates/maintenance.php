<?php

/**
 * Template Name: Maintenance
 * The template for a standalone maintenance page, with no header or footer.
 *
 * @package  WordPress
 * @subpackage  Timber
 */

use P4\MasterTheme\Context;
use Timber\Timber;

global $post;

$context = Timber::context();
$timber_post = Timber::get_post($post->ID);

$context['post'] = $timber_post;
$context['custom_body_classes'] = 'maintenance-page';
$context['page_category'] = 'Maintenance Page';

Context::set_og_meta_fields($context, $timber_post);

Timber::render('maintenance.twig', $context);
