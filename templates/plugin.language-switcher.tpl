<div id="adm_language_switcher" class="dropdown me-2">
    <button class="btn btn-link text-decoration-none" type="button" data-bs-toggle="dropdown"
            aria-expanded="false" aria-label="{$l10n->get('PLG_LANGUAGE_SWITCHER_LABEL')}: {$languageSwitcherCurrentName}" title="{$l10n->get('PLG_LANGUAGE_SWITCHER_LABEL')}">
        <i class="bi bi-globe"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        {foreach $languageSwitcherLanguages as $code => $name}
            <li>
                <a class="dropdown-item{if $code == $languageSwitcherCurrent} active{/if}" lang="{$code}"
                   href="{$languageSwitcherUrl}?language={$code|escape:'url'}"
                   {if $code == $languageSwitcherCurrent}aria-current="true"{/if}>
                    {$name}
                    {if $code == $languageSwitcherCurrent}<i class="bi bi-check2"></i>{/if}
                </a>
            </li>
        {/foreach}
    </ul>
</div>
