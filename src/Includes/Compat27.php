<?php

/**
 * Attempts to add backwards compatiblity by aliasing namespaces classes to 
 * their original global counterparts. This should allow many Extensions,
 * Data-sources, Events, etc to continue functioning, although this is not
 * a guarantee.
 */

$funcGenerateClassAliases = function ($sourceClassname, $destClassname=null, $sourceNamespace="\\Symphony\\Symphony", $destNamespace=null): void {

    if(null == $destClassname || strlen(trim($destClassname)) == 0) {
        $destClassname = $sourceClassname;
    }

    $fqnsSource = "{$sourceNamespace}\\{$sourceClassname}";
    $fqnsDest = "{$destNamespace}\\{$destClassname}";

    // Prompts the SPL autoloader to include this class. This won't be
    // needed once all Symphony classes have been updated with namespaces and
    // can be included via composers PSR-4 autoload.

    if (false == class_exists($fqnsSource, true) && false == interface_exists($fqnsSource, true) && false == trait_exists($fqnsSource, true)) {
        throw new Symphony\Exceptions\SymphonyException("The target class '{$fqnsSource}' could not be located");
    }

    if (true == class_exists($fqnsDest) && true == interface_exists($fqnsDest) && true == trait_exists($fqnsDest)) {
        throw new Symphony\Exceptions\SymphonyException("The destination class alias '{$fqnsDest}' already exists");
    }

    // Create aliases to maintain backwards compatiblity with older core code
    // and extensions. Hopefully this can be removed one day...
    class_alias($fqnsSource, $fqnsDest);
};

foreach(
    [
        ["Symphony"],
        ["Administration"],
        ["Frontend"],
        
        ["Author"],
        ["Session"],
        ["Widget"],
        ["Cookie"],
        ["Entry"],
        ["Lang"],
        ["Configuration"],
        ["Sortable"],
        ["DateTimeObj"],
        ["Log"],
        ["EmailHelper"],
        ["Cacheable"],
        ["General"],
        ["Cryptography"],
        ["EventMessages"],
        ["Gateway"],
        ["Mutex"],
        ["Section"],
        ["Profiler"],
        ["TimestampValidator"],
        ["XsltProcess"],
        ["Installer"],
        ["Updater"],

        ["AbstractMigration", "Migration"],
        ["AbstractField", "Field"],
        ["AbstractTextFormatter", "TextFormatter"],
        ["AbstractDevKitPage", "DevKit"],
        ["AbstractExtension", "Extension"],
        ["XmlElement", "XMLElement"],
        ["Smtp", "SMTP"],
        ["Json", "JSON"],
        ["Xsrf", "XSRF"],
        ["XmlElementChildrenFilter", "XMLElementChildrenFilter"],
        ["AbstractPage", "Page"],
        ["AbstractTextPage", "TextPage"],
        ["AbstractXmlPage", "XMLPage"],
        ["AbstractXsltPage", "XSLTPage"],
        ["AbstractJsonPage", "JSONPage"],
        ["HtmlPage", "HTMLPage"],
        ["AbstractResourcesPage", "ResourcesPage"],

        ["MySql", "MySQL", "\\Symphony\\Symphony\\DatabaseWrappers"],

        ["Page", "InstallerPage", "\\Symphony\\Symphony\\Installer"],

        ["Page", "UpdaterPage", "\\Symphony\\Symphony\\Updater"],

        ["Page", "FrontendPage", "\\Symphony\\Symphony\\Frontend"],

        ["AuthorManager", null, "\\Symphony\\Symphony\\Managers"],
        ["DatasourceManager", null, "\\Symphony\\Symphony\\Managers"],
        ["EmailGatewayManager", null, "\\Symphony\\Symphony\\Managers"],
        ["EntryManager", null, "\\Symphony\\Symphony\\Managers"],
        ["EventManager", null, "\\Symphony\\Symphony\\Managers"],
        ["ExtensionManager", null, "\\Symphony\\Symphony\\Managers"],
        ["FieldManager", null, "\\Symphony\\Symphony\\Managers"],
        ["PageManager", null, "\\Symphony\\Symphony\\Managers"],
        ["ResourceManager", null, "\\Symphony\\Symphony\\Managers"],
        ["SectionManager", null, "\\Symphony\\Symphony\\Managers"],
        ["TextFormatterManager", null, "\\Symphony\\Symphony\\Managers"],

        ["GenericExceptionHandler", null, "\\Symphony\\Symphony\\Handlers"],
        ["GenericErrorHandler", null, "\\Symphony\\Symphony\\Handlers"],
        ["FrontendPageNotFoundExceptionHandler", null, "\\Symphony\\Symphony\\Handlers"],
        ["SymphonyErrorPageExceptionHandler", "SymphonyErrorPageHandler", "\\Symphony\\Symphony\\Handlers"],
        ["DatabaseExceptionHandler", null, "\\Symphony\\Symphony\\Handlers"],

        ["AbstractPage", "AdministrationPage", "\\Symphony\\Symphony\\Administration"],
        ["Alert", null, "\\Symphony\\Symphony\\Administration"],

        ["Sendmail", "SendmailGateway", "\\Symphony\\Symphony\\EmailGateways"],
        ["Smtp", "SMTPGateway", "\\Symphony\\Symphony\\EmailGateways"],

        ["DatabaseException", null, "\\Symphony\\Symphony\\Exceptions"],
        ["EmailException", null, "\\Symphony\\Symphony\\Exceptions"],
        ["EmailGatewayException", null, "\\Symphony\\Symphony\\Exceptions"],
        ["EmailValidationException", null, "\\Symphony\\Symphony\\Exceptions"],
        ["SymphonyErrorPageException", "SymphonyErrorPage", "\\Symphony\\Symphony\\Exceptions"],
        ["FrontendPageNotFoundException", null, "\\Symphony\\Symphony\\Exceptions"],
        ["SMTPException", null, "\\Symphony\\Symphony\\Exceptions"],

        ["CacheInterface", "iCache", "\\Symphony\\Symphony\\Interfaces"],
        ["DatasourceInterface", "iDatasource", "\\Symphony\\Symphony\\Interfaces"],
        ["EventInterface", "iEvent", "\\Symphony\\Symphony\\Interfaces"],
        ["ExportableFieldInterface", "ExportableField", "\\Symphony\\Symphony\\Interfaces"],
        ["FileResourceInterface", "FileResource", "\\Symphony\\Symphony\\Interfaces"],
        ["ImportableFieldInterface", "ImportableField", "\\Symphony\\Symphony\\Interfaces"],
        ["NamespacedCacheInterface", "iNamespacedCache", "\\Symphony\\Symphony\\Interfaces"],
        ["ProviderInterface", "iProvider", "\\Symphony\\Symphony\\Interfaces"],
        ["SingletonInterface", "Singleton", "\\Symphony\\Symphony\\Interfaces"],
    ] as $alias) {
    $funcGenerateClassAliases(...$alias);
}
