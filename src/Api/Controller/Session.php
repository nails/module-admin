<?php

/**
 * Admin API end points: session
 *
 * @package     Nails
 * @subpackage  module-admin
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\Admin\Api\Controller;

use ApiRouter;
use DateTime;
use Nails\Admin\Constants;
use Nails\Admin\Controller\BaseApi;
use Nails\Admin\Model;
use Nails\Admin\Resource;
use Nails\Admin\Traits\Api\RestrictToAdmin;
use Nails\Api;
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\ModelException;
use Nails\Common\Exception\NailsException;
use Nails\Common\Exception\ValidationException;
use Nails\Common\Helper\Model\Expand;
use Nails\Common\Service\FormValidation;
use Nails\Common\Service\HttpCodes;
use Nails\Common\Service\Uri;
use Nails\Factory;
use ReflectionException;
use stdClass;

/**
 * Class Session
 *
 * @package Nails\Admin\Api\Controller
 */
class Session extends BaseApi
{
    use RestrictToAdmin;

    // --------------------------------------------------------------------------

    protected Model\Session $oModel;
    protected Uri           $oUri;

    // --------------------------------------------------------------------------

    /**
     * Session constructor.
     *
     * @param ApiRouter $oApiRouter
     *
     * @throws FactoryException
     * @throws NailsException
     * @throws ReflectionException
     */
    public function __construct(ApiRouter $oApiRouter)
    {
        parent::__construct($oApiRouter);

        $this->oModel = Factory::model('Session', Constants::MODULE_SLUG);
        $this->oUri   = Factory::service('Uri');
    }

    // --------------------------------------------------------------------------

    /**
     * Route all POST requests
     *
     * @throws Api\Exception\ApiException
     * @throws FactoryException
     * @throws ModelException
     * @throws ValidationException
     */
    public function postRemap(): Api\Factory\ApiResponse
    {
        switch ($this->oUri->segment(5)) {
            case 'destroy':
                $oSession = $this->getSession();
                return $this->delete($oSession);
            default:
                return $this->create();
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Route all PUT requests
     *
     * @throws Api\Exception\ApiException
     * @throws FactoryException
     * @throws ModelException
     * @throws ValidationException
     */
    public function putRemap(): Api\Factory\ApiResponse
    {
        $oSession = $this->getSession();

        return match ($this->oUri->segment(5)) {
            'heartbeat' => $this->pulse($oSession),
            'inactive' => $this->setInactive($oSession),
            default => throw new Api\Exception\ApiException('Invalid request'),
        };
    }

    // --------------------------------------------------------------------------

    /**
     * Route all DELETE requests
     *
     * @throws Api\Exception\ApiException
     * @throws FactoryException
     * @throws ModelException
     * @throws ValidationException
     */
    public function deleteRemap(): Api\Factory\ApiResponse
    {
        $oSession = $this->getSession();

        return match ($this->oUri->segment(5)) {
            'inactive' => $this->setActive($oSession),
            default => $this->delete($oSession),
        };
    }

    // --------------------------------------------------------------------------

    /**
     * Create a new admin session
     *
     * @return Api\Factory\ApiResponse
     * @throws FactoryException
     * @throws ModelException
     * @throws ValidationException
     */
    public function create(): Api\Factory\ApiResponse
    {
        $aData = $this->getRequestData();
        /** @var FormValidation $oValidation */
        $oValidation = Factory::service('FormValidation');
        /** @var DateTime $oNow */
        $oNow = Factory::factory('DateTime');

        $oValidation
            ->buildValidator([
                'url' => [
                    FormValidation::RULE_REQUIRED,
                    FormValidation::RULE_VALID_URL,
                ],
            ])
            ->run($aData);

        $aUrl = parse_url($aData['url']);

        /** @var Resource\Session $oSession */
        $oSession = $this->oModel->create([
            'user_id'   => activeUser('id'),
            'url'       => $aUrl['path'] ?? '/',
            'heartbeat' => $oNow->format('Y-m-d H:i:s'),
        ], true);

        return $this->response(
            [
                'token' => $oSession->token,
                'here'  => $this->getOtherSessions($oSession),
            ],
            HttpCodes::STATUS_CREATED
        );
    }

    // --------------------------------------------------------------------------

    /**
     * Update the session's heartbeat
     *
     * @param Resource\Session $oSession The session to update
     *
     * @return Api\Factory\ApiResponse
     * @throws FactoryException
     * @throws ModelException
     * @throws ValidationException
     */
    protected function pulse(Resource\Session $oSession): Api\Factory\ApiResponse
    {
        $this->updateTimestamp($oSession, 'heartbeat');

        return $this->response([
            'here' => $this->getOtherSessions($oSession),
        ]);
    }

    // --------------------------------------------------------------------------

    /**
     * Set a session as inactive
     *
     * @param Resource\Session $oSession The session to update
     *
     * @return Api\Factory\ApiResponse
     * @throws FactoryException
     * @throws ModelException
     * @throws ValidationException
     */
    protected function setInactive(Resource\Session $oSession): Api\Factory\ApiResponse
    {
        $this
            ->updateTimestamp($oSession, 'inactive')
            ->updateTimestamp($oSession, 'heartbeat');

        return $this->response([
            'here' => $this->getOtherSessions($oSession),
        ]);
    }

    // --------------------------------------------------------------------------

    /**
     * Set a session as active
     *
     * @param Resource\Session $oSession The session to update
     *
     * @return Api\Factory\ApiResponse
     * @throws FactoryException
     * @throws ModelException
     * @throws ValidationException
     */
    protected function setActive(Resource\Session $oSession): Api\Factory\ApiResponse
    {
        $this
            ->updateTimestamp($oSession, 'inactive', null)
            ->updateTimestamp($oSession, 'heartbeat');

        return $this->response([
            'here' => $this->getOtherSessions($oSession),
        ]);
    }

    // --------------------------------------------------------------------------

    /**
     * Delete a session
     *
     * @param Resource\Session $oSession The session to delete
     *
     * @throws Api\Exception\ApiException
     * @throws FactoryException
     * @throws ModelException
     * @throws ValidationException
     */
    public function delete(Resource\Session $oSession): Api\Factory\ApiResponse
    {
        if (!$this->oModel->delete($oSession->id)) {
            throw new Api\Exception\ApiException('Failed to delete session. ' . $this->oModel->lastError());
        }

        return $this->response();
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the current session
     *
     * @return Resource\Session
     * @throws Api\Exception\ApiException
     * @throws ModelException
     */
    private function getSession(): Resource\Session
    {
        /** @var Resource\Session $oSession */
        $oSession = $this->oModel->getByToken($this->oUri->segment(4));

        if (empty($oSession) || $oSession->user_id !== activeUser('id')) {
            throw new Api\Exception\ApiException(
                'Invalid session token',
                HttpCodes::STATUS_BAD_REQUEST
            );
        }

        return $oSession;
    }

    // --------------------------------------------------------------------------

    /**
     * Updates a session timestamp
     *
     * @param Resource\Session $oSession The session to update
     * @param string           $sColumn The column to update
     * @param string|null      $sValue The value to set
     *
     * @throws FactoryException
     * @throws ModelException
     */
    private function updateTimestamp(Resource\Session $oSession, string $sColumn, ?string $sValue = 'now'): self
    {
        $sValue = $sValue === 'now'
            ? Factory::factory('DateTime')->format('Y-m-d H:i:s')
            : $sValue;

        $this->oModel->update(
            $oSession->id,
            [
                $sColumn => $sValue,
            ]
        );

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Gets other sessions from the same page
     *
     * @param Resource\Session|null $oSession
     *
     * @return stdClass[]
     * @throws FactoryException
     * @throws ModelException
     */
    private function getOtherSessions(?Resource\Session $oSession): array
    {
        if (empty($oSession)) {
            return [];
        }

        /** @var Model\Session $oModel */
        $oModel = Factory::model('Session', Constants::MODULE_SLUG);

        /** @var Resource\Session[] $aSessions */
        $aSessions = $oModel->getAll([
            new Expand('user'),
            'where' => [
                ['url', $oSession->url],
                ['user_id !=', $oSession->user_id],
                ['token !=', $oSession->token],
                ['heartbeat >', 'DATE_SUB(NOW(), INTERVAL 1 MINUTE)', false],
            ],
        ]);

        return array_map(function (Resource\Session $oSession) {
            return (object) [
                'user'     => (object) [
                    'id'   => $oSession->user->id,
                    'name' => $oSession->user->name,
                ],
                'created'  => $oSession->created->relative(false),
                'inactive' => $oSession->inactive?->relative(false),
            ];
        }, $aSessions);
    }

    // --------------------------------------------------------------------------

    /**
     * Build an ApiResponse
     *
     * @param array|null $aData
     * @param int|null   $iCode
     *
     * @return Api\Factory\ApiResponse
     * @throws FactoryException
     * @throws ValidationException
     */
    private function response(?array $aData = null, ?int $iCode = null): Api\Factory\ApiResponse
    {
        /** @var Api\Factory\ApiResponse $oApiResponse */
        $oApiResponse = Factory::factory('ApiResponse', Api\Constants::MODULE_SLUG);

        if ($iCode) {
            $oApiResponse->setCode($iCode);
        }

        if (!empty($aData)) {
            $oApiResponse->setData($aData);
        }

        return $oApiResponse;
    }
}
