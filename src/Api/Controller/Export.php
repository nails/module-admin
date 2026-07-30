<?php

namespace Nails\Admin\Api\Controller;

use DateInterval;
use DateMalformedIntervalStringException;
use DateTime;
use Nails\Admin\Admin\Permission;
use Nails\Admin\Constants;
use Nails\Admin\Service\DataExport;
use Nails\Admin\Traits\Api\RestrictToAdmin;
use Nails\Api;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\NailsException;
use Nails\Common\Exception\ValidationException;
use Nails\Common\Helper\Model\Expand;
use Nails\Common\Helper\Model\Sort;
use Nails\Common\Helper\Model\Where;
use Nails\Common\Resource;
use Nails\Common\Service\FormValidation;
use Nails\Factory;
use ReflectionException;
use stdClass;

/**
 * Class Export
 *
 * @package Nails\Admin\Api\Controller
 */
class Export extends Api\Controller\CrudController
{
    use RestrictToAdmin;

    // --------------------------------------------------------------------------

    const CONFIG_MODEL_NAME     = 'Export';
    const CONFIG_MODEL_PROVIDER = Constants::MODULE_SLUG;

    // --------------------------------------------------------------------------

    private DataExport $exportService;

    // --------------------------------------------------------------------------

    /**
     * @param $oApiRouter
     *
     * @throws Api\Exception\ApiException
     * @throws FactoryException
     * @throws NailsException
     * @throws ReflectionException
     */
    public function __construct($oApiRouter)
    {
        parent::__construct($oApiRouter);
        $this->exportService = Factory::service('DataExport', Constants::MODULE_SLUG);
    }

    // --------------------------------------------------------------------------

    public static function requirePermission(): ?string
    {
        return Permission\Utilities\DataExport\Generate::class;
    }

    // --------------------------------------------------------------------------

    /**
     * @throws FactoryException
     */
    protected function getLookupData(string $sMode, array $aData): array
    {
        /** @var DateTime $now */
        $now = Factory::factory('DateTime');

        return array_merge(
            parent::getLookupData($sMode, $aData),
            [
                new Expand('user'),
                new Where('user_id', activeUser('id')),
                new Where('expires >', $now->format('Y-m-d H:i:s')),
                new Sort('created', Sort::DESC),
            ]
        );
    }

    // --------------------------------------------------------------------------

    /**
     * @throws ValidationException
     * @throws FactoryException
     * @throws DateMalformedIntervalStringException
     */
    protected function validateUserInput($aData, ?Resource\Entity $oItem = null)
    {
        /** @var DataExport $service */
        $service = Factory::service('DataExport', Constants::MODULE_SLUG);
        /** @var DateTime $expires */
        $expires = Factory::factory('DateTime');
        $expires->add(new DateInterval('PT' . $service->getRetentionPeriod() . 'S'));

        $out = [
            'user_id' => activeUser('id'),
            'format'  => $aData['format'] ?? null,
            'source'  => $aData['source'] ?? null,
            'options' => $aData['options'] ?? '{}', // Expects a JSON string
            'expires' => $expires->format('Y-m-d H:i:s'),
        ];

        /** @var FormValidation $formValidation */
        $formValidation = Factory::service('FormValidation');

        $formValidation
            ->buildValidator([
                'source'  => [
                    FormValidation::RULE_REQUIRED,
                    function ($value) {
                        if (!$this->exportService->getSourceBySlug($value)) {
                            throw new ValidationException('Invalid source.');
                        }
                    },
                ],
                'format'  => [
                    FormValidation::RULE_REQUIRED,
                    function ($value) {
                        if (!$this->exportService->getFormatBySlug($value)) {
                            throw new ValidationException('Invalid format.');
                        }
                    },
                ],
                'options' => [
                    FormValidation::RULE_REQUIRED,
                ],
            ])
            ->run($out);

        return $out;
    }

    // --------------------------------------------------------------------------

    /**
     * Formats the response object
     *
     * @param stdClass $oObj The object to format
     *
     * @return stdClass
     */
    protected function formatObject($oObj): stdClass
    {
        $source = $this->exportService->getSourceBySlug($oObj->source);
        $format = $this->exportService->getFormatBySlug($oObj->format);

        return (object) [
            'id'       => $oObj->id,
            'method'   => $oObj->method,
            'source'   => $source ? [
                'slug'        => $source->slug,
                'label'       => $source->label,
                'description' => $source->description,
            ] : null,
            'options'  => json_decode($oObj->options),
            'format'   => $format ? [
                'slug'        => $format->slug,
                'label'       => $format->label,
                'description' => $format->description,
            ] : null,
            'status'   => $oObj->status,
            'error'    => $oObj->error,
            'download' => $oObj->download_id ? [
                'id'  => $oObj->download_id,
                'url' => cdnServe($oObj->download_id, true),
            ] : null,
            'expires'  => $oObj->expires,
            'created'  => $oObj->created,
            'user'     => $oObj->user ? [
                'id'    => $oObj->user->id,
                'name'  => $oObj->user->name,
                'email' => $oObj->user->email,
            ] : null,
            'modified' => $oObj->modified,
        ];
    }
}
