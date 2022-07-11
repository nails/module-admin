<?php

namespace Nails\Admin\Resource;

use Nails\Auth\Constants;
use Nails\Auth\Resource\User;
use Nails\Common\Resource\Entity;
use Nails\Factory;

/**
 * Class ChangeLog
 *
 * @package Nails\Admin\Resource
 */
class ChangeLog extends Entity
{
    /** @var int */
    public $user_id;

    /** @var User */
    public $user;

    /** @var string */
    public $verb;

    /** @var string */
    public $article;

    /** @var string */
    public $item;

    /** @var int */
    public $item_id;

    /** @var string */
    public $title;

    /** @var string */
    public $url;

    /** @var string */
    public $changes;

    // --------------------------------------------------------------------------

    public function getUser(): ?User
    {
        if (empty($this->user) && !empty($this->user_id)) {
            $oModel     = Factory::model('User', Constants::MODULE_SLUG);
            $this->user = $oModel->getById($this->user_id);
        }

        return $this->user;
    }

    // --------------------------------------------------------------------------

    public function getSentence(): string
    {
        if (class_exists($this->item) && classImplements($this->item, \Nails\Admin\Interfaces\ChangeLog::class)) {
            $sType    = call_user_func($this->item . '::getChageLogTypeLabel');
            $sTypeUrl = call_user_func($this->item . '::getChageLogTypeUrl');
        } else {
            $sType    = $this->item;
            $sTypeUrl = null;
        }

        return implode(' ', array_filter([
            $this->getUser()
                ? anchor('admin/auth/accounts/edit/' . $this->getUser()->id, $this->getUser()->name)
                : 'Someone',
            $this->verb,
            $this->article,
            $sTypeUrl
                ? anchor($sTypeUrl, $sType)
                : $sType,
            '&mdash;',
            $this->url
                ? '<strong>' . anchor($this->url, $this->title) . '</strong>'
                : $this->title,
        ]));
    }

    // --------------------------------------------------------------------------

    public function getChangesAsList(): string
    {
        $aChanges = json_decode($this->changes);
        if (empty($aChanges)) {
            return '';
        }

        return sprintf(
            '<ul>%s</ul>',
            implode(PHP_EOL, array_map(function ($oChange) {
                return sprintf(
                    '<li><strong>%s</strong>: <em>%s</em>&nbsp;&rarr;&nbsp;<em>%s</em></li>',
                    $oChange->field,
                    $oChange->old_value ?: '<span>blank</span>',
                    $oChange->new_value ?: '<span>blank</span>',
                );
            }, $aChanges ?? []))
        );
    }

    public function getChangesAsTable(
        $sTableClass = 'table table-striped table-hover table-bordered table-responsive',
        $sTableHeadClass = 'table-dark',
        $sTablebodyClass = ''
    ): string {
        $aChanges = json_decode($this->changes);
        if (empty($aChanges)) {
            return '';
        }

        return sprintf(
            '<table class="%s">
                <thead class="%s">%s</thead>
                <tbody class="%s">%s</tbody>
            </table>',
            $sTableClass,
            $sTableHeadClass,
            implode(
                PHP_EOL,
                [
                    '<th>Field</th>',
                    '<th>Old</th>',
                    '<th>New</th>',
                ]
            ),
            $sTablebodyClass,
            implode(PHP_EOL, array_map(function ($oChange) {
                return sprintf(
                    '<tr><td><strong>%s</strong></td><td><em>%s</em></td><td><em>%s</em></td></tr>',
                    $oChange->field,
                    $oChange->old_value ?: '<span>blank</span>',
                    $oChange->new_value ?: '<span>blank</span>',
                );
            }, $aChanges ?? []))
        );
    }
}
