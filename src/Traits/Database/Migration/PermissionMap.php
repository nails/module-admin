<?php

namespace Nails\Admin\Traits\Database\Migration;

/**
 * Trait PermissionMap
 *
 * Translates the legacy string permissions held in user groups' ACLs into the
 * permission class names which replaced them.
 *
 * Consuming migrations supply the translations via a MAP constant, keyed by the legacy
 * permission; a value of null or an empty string drops the permission entirely:
 *
 * ```
 * const MAP = [
 *     'admin:example:thing:browse' => Permission\Thing\Browse::class,
 *     'admin:example:thing:create' => '',
 * ];
 * ```
 *
 * Translation is idempotent - a mapped permission is left alone on subsequent passes -
 * so migrations using this trait are safe to declare as Repeatable.
 *
 * Must be used alongside Nails\Common\Traits\Database\Migration, which provides the
 * database access below.
 *
 * @package Nails\Admin\Traits\Database\Migration
 */
trait PermissionMap
{
    abstract public function query(string $sQuery): \PDOStatement;

    abstract public function prepare(string $sQuery): \PDOStatement;

    abstract protected function tableExists(string $sTable): bool;

    // --------------------------------------------------------------------------

    /**
     * Execute the migration
     *
     * @return void
     */
    public function execute(): void
    {
        //  On a fresh build, this table might not yet exist
        if (!$this->tableExists('{{NAILS_DB_PREFIX}}user_group')) {
            return;
        }

        $oUpdate = $this->prepare('UPDATE `{{NAILS_DB_PREFIX}}user_group` SET `acl` = :acl WHERE `id` = :id');
        $oResult = $this->query('SELECT `id`, `acl` FROM `{{NAILS_DB_PREFIX}}user_group`');

        while ($oRow = $oResult->fetchObject()) {

            $aAcl = json_decode($oRow->acl ?? 'null', true);
            if (!is_array($aAcl)) {
                continue;
            }

            $aAcl = array_values($aAcl);
            $aNew = array_map(fn($mPermission) => $this->mapPermission((string) $mPermission), $aAcl);
            $aNew = array_values(array_unique(array_filter($aNew)));

            //  Nothing to translate; don't churn the row on every migration
            if ($aNew === $aAcl) {
                continue;
            }

            $oUpdate->execute([
                ':id'  => $oRow->id,
                ':acl' => json_encode($aNew),
            ]);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Translates a single permission
     *
     * Anything not in the map is passed through untouched, which is what makes this
     * safe to run repeatedly - already translated permissions are not in the map.
     * Override to apply translations which a simple map cannot express.
     *
     * @param string $sPermission The permission to translate
     *
     * @return string|null The replacement permission, or null to drop it
     */
    protected function mapPermission(string $sPermission): ?string
    {
        return static::MAP[$sPermission] ?? $sPermission;
    }
}
