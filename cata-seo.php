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
 * Version:     0.1.0
 * License:     GPL v3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Cata\SEO;

/**
 * Structured Data
 */
require_once __DIR__ . '/includes/structured-data/class-structured-data.php';

new Structured_Data();
