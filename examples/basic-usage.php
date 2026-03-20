<?php

declare(strict_types=1);

/**
 * Example: Working with the ps_languageselector PrestaShop module.
 *
 * ps_languageselector renders a language switcher dropdown in the storefront
 * header. When a customer selects a language, the page reloads with translated
 * content and the ISO code appears in the URL (e.g., /fr/ or /es/).
 *
 * This file documents common usage patterns.
 */

// --- Widget invocation in Smarty/Twig template ---
// {widget name="ps_languageselector" hook="displayNav1"}

// --- Querying available languages programmatically ---
// $languages = Language::getLanguages(active: true, id_shop: Context::getContext()->shop->id);
//
// foreach ($languages as $lang) {
//     echo $lang['name'] . ' (' . $lang['iso_code'] . '): ' . $lang['language_code'] . "\n";
// }
// Example output:
//   English (en): en-us
//   French (fr): fr-fr
//   Spanish (es): es-es

// --- Getting the current language ---
// $context  = Context::getContext();
// $language = $context->language;
// echo "Current: {$language->name} ({$language->iso_code})\n";

// --- Language switching happens via URL ---
// Switching language redirects to the same page with the new language prefix:
//   Current: https://shop.com/en/summer-dresses
//   French:  https://shop.com/fr/robes-été

// --- Adding a language (Back Office) ---
// International > Translations > Add / Update a language
// Or: International > Localization > Localization pack (imports language + locale)

// --- Template override ---
// themes/{theme}/modules/ps_languageselector/views/templates/hook/ps_languageselector.tpl

// --- Hook placements ---
// Default: displayNav1 (top navigation bar, alongside currency selector)
// Also supports: displayNav2, displayHeader
