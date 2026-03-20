<?php

declare (strict_types=1);
namespace Prettus\Repository\Traits;

/**
 * Trait ComparesVersionsTrait
 * @package Prettus\Repository\Traits
 * @author Anderson Andrade <contato@andersonandra.de>
 */
trait Compares_Versions_Trait
{
    /**
     * Version compare function that can compare both Laravel and Lumen versions.
     *
     * @param   string      $frameworkVersion
     * @param   string      $compareVersion
     * @param   string|null $operator
     */
    public function version_compare($framework_version, $compare_version, $operator = null): bool
    {
        // Lumen (5.5.2) (Laravel Components 5.5.*)
        $lumen_pattern = '/Lumen \((\d\.\d\.[\d|\*])\)( \(Laravel Components (\d\.\d\.[\d|\*])\))?/';
        if (preg_match($lumen_pattern, $framework_version, $matches)) {
            $framework_version = $matches[3] ?? $matches[1];
            // Prefer Laravel Components version.
        }
        return version_compare($framework_version, $compare_version, $operator);
    }
}