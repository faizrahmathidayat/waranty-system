<?php

namespace App\Services;

/**
 * Business mapping for automotive orders.  Keeping this mapping here avoids
 * duplicating area rules in controllers and JavaScript.
 */
class AutomotiveOrderCatalog
{
    /** The only areas an Automotive order item can use. */
    public const VEHICLE_AREAS = ['Kaca Depan', 'Samping Belakang', 'Samping Depan', 'Belakang', 'Sunroof / Panoramic'];
}
