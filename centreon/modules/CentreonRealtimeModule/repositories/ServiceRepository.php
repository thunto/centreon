<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

namespace CentreonRealtime\Repository;

use \CentreonConfiguration\Repository\HostRepository as HostConfigurationRepository,
    \CentreonConfiguration\Repository\ServiceRepository as ServiceConfigurationRepository,
    \Centreon\Internal\Utils\Datetime,
    \Centreon\Internal\Di;

/**
 * @author Sylvestre Ho <sho@merethis.com>
 * @package CentreonRealtime
 * @subpackage Repository
 */
class ServiceRepository extends \CentreonRealtime\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'services';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Service';

    /**
     *
     * @var string
     */
    public static $objectId = 'service_id';

    /**
     *
     * @var string
     */
    public static $hook = 'displayServiceRtColumn';

    /**
     *
     * @var array Default column for datatable
     */
    public static $datatableColumn = array(
        '<input id="allService" class="allService" type="checkbox">' => 'service_id',
        'Host Name' => 'name',
        'Service Name' => 'description',
        'Ico' => "'<i class=\'fa fa-bar-chart-o\'></i>' as ico",
        'Status' => 'services.state',
        'Last Check' => 'services.last_check',
        'Duration' => '[SPECFIELD](unix_timestamp(NOW())-services.last_hard_state_change) AS duration',
        'Retry' => "CONCAT(services.check_attempt, ' / ', services.max_check_attempts) as retry",
        'Output' => 'services.output'
    );
    
    /**
     *
     * @var type 
     */
    public static $additionalColumn = array('h.host_id');
    
    /**
     *
     * @var array 
     */
    public static $researchIndex = array(
        'service_id',
        'name',
        'description',
        "'<i class=\'fa fa-bar-chart-o\'></i>' as ico",
        'services.last_check',
        'services.state',
        '[SPECFIELD](unix_timestamp(NOW())-services.last_hard_state_change) AS duration',
        "CONCAT(services.check_attempt, ' / ', services.max_check_attempts) as retry",
        'services.output'
    );
    
    /**
     *
     * @var string 
     */
    public static $specificConditions = "h.host_id = services.host_id AND services.enabled = 1 ";
    
    /**
     *
     * @var string 
     */
    public static $linkedTables = "hosts h";
    
    /**
     *
     * @var array 
     */
    public static $datatableHeader = array(
        'none',
        'text',
        'text',
        'none',
        array('select' => array(
                'OK' => 0,
                'Warning' => 1,
                'Critical' => 2,
                'Unknown' => 3,
                'Pending' => 4
            )
        ),
        'text',
        'text',
        'text',
        'text'
    );
    
    /**
     *
     * @var array 
     */
    public static $columnCast = array(
        'state' => array(
            'type' => 'select',
            'parameters' =>array(
                '0' => '<span class="label label-success">OK</span>',
                '1' => '<span class="label label-warning">Warning</span>',
                '2' => '<span class="label label-danger">Critical</span>',
                '3' => '<span class="label label-default">Unknown</span>',
                '4' => '<span class="label label-info">Pending</span>',
            )
        ),
        'service_id' => array(
            'type' => 'checkbox',
            'parameters' => array(
                'displayName' => '::service_description::'
            )
        ),
        'description' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/realtime/service/[i:id]',
                'routeParams' => array(
                    'id' => '::service_id::'
                ),
                'linkName' => '::description::'
            )
        ),
        'name' => array(
            'type' => 'url',
            'parameters' => array(
                'route' => '/realtime/host/[i:id]',
                'routeParams' => array(
                    'id' => '::host_id::'
                ),
                'linkName' => '::name::'
            )
        )
    );
    
    /**
     *
     * @var array 
     */
    public static $datatableFooter = array(
        'none',
        'text',
        'text',
        'text',
        array('select' => array(
                'OK' => 0,
                'Warning' => 1,
                'Critical' => 2,
                'Unknown' => 3,
                'Pending' => 4
            )
        ),
        'text', 
        'text',
        'text',
        'text'
    );
    
    /**
     * Format data for datatable
     * 
     * @param array $resultSet
     */
    public static function formatDatas(&$resultSet)
    {
        $previousHost = '';
        foreach ($resultSet as &$myServiceSet) {
            // Set host_name
            if ($myServiceSet['name'] === $previousHost) {
                $myServiceSet['name'] = '';
            } else {
                $previousHost = $myServiceSet['name'];
                $icon = HostConfigurationRepository::getIconImage($myServiceSet['name']);
                $myServiceSet['name'] = '<span class="rt-tooltip">'.
                    $icon.
                    '&nbsp;'.$myServiceSet['name'].'</span>';
            }
            $icon = ServiceConfigurationRepository::getIconImage($myServiceSet['service_id']);
            $myServiceSet['description'] = '<span class="rt-tooltip">'.
                $icon.
                '&nbsp;'.$myServiceSet['description'].'</span>';
            $myServiceSet['duration'] = Datetime::humanReadable(
                                                                $myServiceSet['duration'],
                                                                Datetime::PRECISION_FORMAT,
                                                                2
                                                                ); 
        }
    }

    /**
     * Get service status
     *
     * @param int $host_id
     * @param int $service_id
     * @return mixed
     */
    public static function getStatus($host_id, $service_id)
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_storage');
        
        $stmt = $dbconn->prepare('SELECT last_hard_state as state 
            FROM services 
            WHERE service_id = ? 
            AND host_id = ? 
            AND enabled = 1 
            LIMIT 1');
        $stmt->execute(array($service_id, $host_id));
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row['state'];
        }
        return -1;
    }

    /**
     * Format small badge status
     *
     * @param int $status
     * @return string
     */
    public static function getStatusBadge($status) 
    {
        switch ($status) {
            case 0:
                $status = "label-success";
                break;
            case 1:
                $status = "label-warning";
                break;
            case 2:
                $status = "label-danger";
                break;
            case 3:
                $status = "label-default";
                break;
            case 4:
                $status = "label-info";
                break;
            default:
                $status = "";
                break;
        }
        return "<span class='label $status pull-right overlay'>&nbsp;</span>";
    }

     
}
