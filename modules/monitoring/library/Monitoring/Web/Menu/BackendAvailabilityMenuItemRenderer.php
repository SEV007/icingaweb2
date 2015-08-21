<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Monitoring\Web\Menu;

use Icinga\Web\Menu;
use Icinga\Web\Menu\BadgeMenuItemRenderer;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;

class BackendAvailabilityMenuItemRenderer extends BadgeMenuItemRenderer
{
    /**
     * Get whether or not the monitoring backend is currently running
     *
     * @return bool
     */
    protected function isCurrentlyRunning()
    {
        $programStatus = MonitoringBackend::instance()
            ->select()
            ->from(
                'programstatus',
                array('is_currently_running')
            )
            ->fetchOne();
        return $programStatus !== false ? (bool) $programStatus : false;
    }

    /**
     * The css class of the badge
     *
     * @return string
     */
    public function getState()
    {
        return self::STATE_CRITICAL;
    }

    /**
     *  The amount of items to display in the badge
     *
     * @return int
     */
    public function getCount()
    {
        if (! $this->isCurrentlyRunning()) {
            return 1;
        }
        return 0;
    }

    /**
     * The tooltip title
     *
     * @return string
     * @throws \Icinga\Exception\ConfigurationError
     */
    public function getTitle()
    {
        return sprintf(
            mt('monitoring', 'Monitoring backend %s is not running'),
            MonitoringBackend::instance()->getName()
        );
    }
}
