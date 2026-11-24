<?php

namespace AdmidioPlugin\LanguageSwitcher;

use Admidio\Infrastructure\Plugins\PluginRegistry;
use Admidio\Session\Entity\Session;
use Admidio\UI\Presenter\PagePresenter;

/**
 * Lets every visitor choose the language of the Admidio interface. The choice of a logged-in user
 * is stored in the table of this plugin, so it follows the user to every device. A visitor who is
 * not logged in keeps the choice in a cookie on this device.
 *
 * Only the texts of Admidio and of plugins with this language are translated. Content that was
 * entered by the organization, e.g. announcements or own menu entries, stays as it is.
 *
 * @copyright 2026 Mohammad Jarkas
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 */
final class LanguageSwitcher
{
    public const PLUGIN_ID = 'language-switcher';

    /**
     * Lifetime of the language cookie: one year.
     */
    private const COOKIE_LIFETIME = 31536000;

    /**
     * Name of the cookie that stores the chosen language code.
     * @return string
     */
    private static function getCookieName(): string
    {
        return COOKIE_PREFIX . '_LANGUAGE';
    }

    /**
     * The languages the administrator offers, as language code => native name. Codes that are not
     * installed in Admidio are skipped.
     * @return array<string,string>
     * @throws \Admidio\Infrastructure\Exception
     */
    private static function getOfferedLanguages(): array
    {
        global $gL10n;

        $plugin = PluginRegistry::get(self::PLUGIN_ID);
        if ($plugin === null) {
            return array();
        }

        $available = $gL10n->getAvailableLanguages();
        $setting = (string)($plugin->getSettingValues()['language_switcher_languages'] ?? '');

        $languages = array();
        foreach (explode(',', $setting) as $code) {
            $code = trim($code);
            if (isset($available[$code])) {
                // "German - Deutsch (Sie)" is shown as "Deutsch (Sie)", the name a speaker recognizes
                $parts = explode(' - ', $available[$code], 2);
                $languages[$code] = $parts[1] ?? $parts[0];
            }
        }

        return $languages;
    }

    /**
     * Name of the table that stores the language of each user.
     * @return string
     */
    private static function getTableName(): string
    {
        return TABLE_PREFIX . '_plg_language_switcher';
    }

    /**
     * The language the user has chosen on any device.
     * @param int $userId ID of the user.
     * @return string Returns the language code or an empty string if the user hasn't chosen one.
     * @throws \Admidio\Infrastructure\Exception
     */
    private static function getUserLanguage(int $userId): string
    {
        global $gDb;

        $sql = 'SELECT lsw_language FROM ' . self::getTableName() . ' WHERE lsw_usr_id = ?';

        return (string)$gDb->queryPrepared($sql, array($userId))->fetchColumn();
    }

    /**
     * Store the chosen language of the user for all devices.
     * @param int $userId ID of the user.
     * @param string $code Language code.
     * @return void
     * @throws \Admidio\Infrastructure\Exception
     */
    private static function saveUserLanguage(int $userId, string $code): void
    {
        global $gDb;

        $gDb->startTransaction();
        $gDb->queryPrepared('DELETE FROM ' . self::getTableName() . ' WHERE lsw_usr_id = ?', array($userId));
        $gDb->queryPrepared('INSERT INTO ' . self::getTableName() . ' (lsw_usr_id, lsw_language) VALUES (?, ?)', array($userId, $code));
        $gDb->endTransaction();
    }

    /**
     * Switch to the chosen language if it is one of the offered languages. The language stored for
     * a logged-in user wins over the cookie of the device.
     * @return void
     * @throws \Admidio\Infrastructure\Exception
     */
    public static function applyChosenLanguage(): void
    {
        global $gL10n, $gMenu, $gValidLogin, $gCurrentUserId;

        $code = '';
        if ($gValidLogin) {
            $code = self::getUserLanguage((int)$gCurrentUserId);
        }
        if ($code === '') {
            $code = (string)($_COOKIE[self::getCookieName()] ?? '');
        }
        if ($code === '' || $code === $gL10n->getLanguage() || !array_key_exists($code, self::getOfferedLanguages())) {
            return;
        }

        $gL10n->setLanguage($code);
        // the menu keeps its translated names in the session, so it has to be loaded again
        $gMenu?->initialize();
    }

    /**
     * Store the chosen language for the user and this device and use it from now on.
     * @param string $code Language code, must be one of the offered languages.
     * @return bool Returns **false** if the language is not offered.
     * @throws \Admidio\Infrastructure\Exception
     */
    public static function choose(string $code): bool
    {
        global $gL10n, $gMenu, $gValidLogin, $gCurrentUserId;

        if (!array_key_exists($code, self::getOfferedLanguages())) {
            return false;
        }

        if ($gValidLogin) {
            self::saveUserLanguage((int)$gCurrentUserId, $code);
        }
        Session::setCookie(self::getCookieName(), $code, time() + self::COOKIE_LIFETIME);
        $gL10n->setLanguage($code);
        $gMenu?->initialize();

        return true;
    }

    /**
     * Callback of the **page_before_render** action. Adds the language selection to the header of
     * the page. The header belongs to the theme, so the selection is moved there in the browser;
     * a page without header just doesn't show it.
     * @param PagePresenter $page The page that is rendered.
     * @return void
     * @throws \Admidio\Infrastructure\Exception
     * @throws \Smarty\Exception
     */
    public static function addSelection(PagePresenter $page): void
    {
        global $gL10n;

        $plugin = PluginRegistry::get(self::PLUGIN_ID);
        $languages = self::getOfferedLanguages();
        if ($plugin === null || count($languages) < 2) {
            return;
        }

        $html = $plugin->renderTemplate($page, 'plugin.language-switcher.tpl', array(
            'languageSwitcherLanguages' => $languages,
            'languageSwitcherCurrent' => $gL10n->getLanguage(),
            'languageSwitcherCurrentName' => $languages[$gL10n->getLanguage()] ?? '',
            'languageSwitcherUrl' => $plugin->getUrl('switch.php')
        ));

        $page->addCssFile($plugin->getAssetUrl('css/main.css'));

        $page->addJavascript('
            var languageSwitcherTarget = $("#adm_main_navbar .ms-auto").first();
            if (languageSwitcherTarget.length > 0) {
                languageSwitcherTarget.prepend(' . json_encode($html) . ');
            }',
            true
        );
    }
}
