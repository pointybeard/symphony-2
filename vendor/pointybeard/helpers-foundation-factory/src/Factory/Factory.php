<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Foundation\Factory;

use pointybeard\Helpers\Functions\Strings;

if (!function_exists(__NAMESPACE__.'\create')) {
    function create(string $alias, string $templateNamespace, ?string $expectedClassType = null): string
    {
        if (class_exists($alias)) {
            throw new \Exception(sprintf('Unable to create factory. Returned: Class %s already exists in global scope', $alias));
        }

        $namespace = __NAMESPACE__;
        $wrapperShortName = Strings\random_unique_classname('factoryCreationWrapper', $namespace);
        $wrapper = "{$namespace}\\{$wrapperShortName}";

        /*
         * Unfortunately, resorting to using eval() is necessary here. Anonymous
         * classes will return the same instance if the wrapping class/function
         * is the same. i.e. this function will ALWAYS return the exact same
         * instance of the anonymous class. This is a huge problem since each
         * factory class needs to know how to return the correct values for
         * getTemplateNamespace() and getExpectedClassType()
         *
         * To solve this, the wrapper class name is dynamically generated to
         * ensure a new instance of the anonymous class is always returned.
         *
         * The use of eval() does not get any user generated content
         * so it is not possible for malicious user code to be executed. The use
         * of FactoryRegistry keeps the provided $templateNamespace and
         * $expectedClassType values silo'd away so they cannot modify the
         * resultant class
         */
        eval(sprintf(
            'namespace %s;
            final class %s {
                public static function generate() {
                    return get_class(new class() extends AbstractFactory {
                        public function getTemplateNamespace(): string
                        {
                            [$templateNamespace, ] = ClassRegistry::lookup(self::class);
                            return $templateNamespace;
                        }

                        public function getExpectedClassType(): ?string
                        {
                            [, $expectedClassType] = ClassRegistry::lookup(self::class);
                            return $expectedClassType;
                        }
                    });
                }
            }',
            ltrim($namespace, '\\'),
            $wrapperShortName
        ));

        $classname = $wrapper::generate();
        class_alias($classname, $alias);
        ClassRegistry::register(
            $classname,
            $templateNamespace,
            $expectedClassType
        );
        return $alias;
    }
}
