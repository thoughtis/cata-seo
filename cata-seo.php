<?php
/**
 * Cata SEO
 *
 * @package   Cata\SEO
 * @author    Thought & Expression Co. <devjobs@thought.is>
 * @copyright 2019 Thought & Expression Co.
 * @license   GNU GENERAL PUBLIC LICENSE
 *
 * @wordpress-plugin
 * Plugin Name: Cata SEO
 * Description: Adds features related to search indexing and structured data.
 * Author:      Thought & Expression Co. <devjobs@thought.is>
 * Author URI:  https://thought.is
 * Version:     0.1.1-beta1
 * License:     GPL v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Cata\SEO;

/**
 * Structured Data
 */
require_once __DIR__ . '/includes/structured-data/class-structured-data.php';

new Structured_Data();

/**
 * Jetpack Settings
 */
require_once __DIR__ . '/includes/jetpack-settings/class-jetpack-settings.php';
require_once __DIR__ . '/includes/jetpack-settings/share-image/class-share-image.php';

new Jetpack_Settings();
new Jetpack_Settings\Share_Image();

/**
 * Robots
 */
require_once __DIR__ . '/includes/robots/class-robots.php';

new Robots();
