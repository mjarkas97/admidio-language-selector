<?php
/**
 ***********************************************************************************************
 * Stores the language the visitor has chosen and returns to the page the visitor came from.
 *
 * Parameters:
 *
 * language : Code of the chosen language, e.g. de-DE
 *
 * @copyright 2026 Mohammad Jarkas
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */

use AdmidioPlugin\LanguageSwitcher\LanguageSwitcher;

try {
    require_once(__DIR__ . '/../../../system/common.php');

    $getLanguage = admFuncVariableIsValid($_GET, 'language', 'string', array('requireValue' => true));

    if (!LanguageSwitcher::choose($getLanguage)) {
        $gMessage->show($gL10n->get('SYS_INVALID_PAGE_VIEW'));
        // => EXIT
    }

    // only return to pages of this Admidio installation
    $referer = (string)($_SERVER['HTTP_REFERER'] ?? '');
    if (!str_starts_with($referer, ADMIDIO_URL . '/')) {
        $referer = ADMIDIO_URL . '/';
    }

    admRedirect($referer);
    // => EXIT
} catch (Throwable $e) {
    handleException($e);
}
