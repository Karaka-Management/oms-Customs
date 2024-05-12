<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Customs\Controller\BackendController;
use Modules\Customs\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^/customs/sanction/dashboard(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Customs\Controller\BackendController:viewSanctionDashboard',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::SANCTION,
            ],
        ],
    ],
    '^/customs/sanction/view(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Customs\Controller\BackendController:viewSanctionView',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::SANCTION,
            ],
        ],
    ],
    '^/customs/hscode/dashboard(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Customs\Controller\BackendController:viewHSCodeDashboard',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::HS_CODE,
            ],
        ],
    ],
    '^/customs/hscode/view(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Customs\Controller\BackendController:viewHSCodeView',
            'verb'       => RouteVerb::GET,
            'active'     => true,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::HS_CODE,
            ],
        ],
    ],
];
