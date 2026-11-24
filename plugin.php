<?php
/**
 ***********************************************************************************************
 * Language switcher - entry file.
 *
 * The entry file is included once in every request when the plugin is loaded. It applies the
 * language the visitor has chosen and adds the language selection to the header of every page.
 *
 * @copyright 2026 Mohammad Jarkas
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */

use Admidio\Hooks\Hooks;
use AdmidioPlugin\LanguageSwitcher\LanguageSwitcher;

/*
 * This file is included by the plugin loader and is never an entry point of its own.
 */
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    exit('This page may not be called directly!');
}

LanguageSwitcher::applyChosenLanguage();

Hooks::addAction('page_before_render', array(LanguageSwitcher::class, 'addSelection'), 20, 1, 'language-switcher');
